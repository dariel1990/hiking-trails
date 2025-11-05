<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Event;
use Carbon\Carbon;
use Exception;

class ScrapeSmithersEvents extends Command
{
    protected $signature = 'events:scrape {--debug : Show debug information} {--force : Force scrape and auto-cleanup}';
    protected $description = 'Scrape events from SmithersEvents.com';

    public function handle()
    {
        $this->info('Starting to scrape events from SmithersEvents.com...');
        $this->newLine();

        // DELETE ALL OLD DATA WHEN --force IS USED
        if ($this->option('force')) {
            $this->info('Deleting all existing events...');
            Event::truncate(); // Deletes ALL events from database
            $this->info('✓ All events deleted.');
            $this->newLine();
        }

        try {
            $url = 'https://smithersevents.com/';
            $html = Http::timeout(30)->get($url)->body();
            $crawler = new Crawler($html);

            $stats = [
                'new' => 0,
                'updated' => 0,
                'errors' => 0,
            ];

            // Find all event articles
            $eventNodes = $crawler->filter('section.event-list article.clearfix');
            
            if ($eventNodes->count() === 0) {
                $this->error('No events found on SmithersEvents.com');
                return 1;
            }

            $this->info('Found ' . $eventNodes->count() . ' events. Processing...');
            $this->newLine();

            $bar = $this->output->createProgressBar($eventNodes->count());
            $bar->start();

            $eventNodes->each(function (Crawler $node, $index) use (&$stats, $bar) {
                try {
                    // Extract data from visible elements
                    $title = $node->filter('h3 a')->count() ? trim($node->filter('h3 a')->text()) : null;
                    $eventUrl = $node->filter('h3 a')->count() ? $node->filter('h3 a')->attr('href') : null;
                    
                    // Make URL absolute
                    if ($eventUrl && !filter_var($eventUrl, FILTER_VALIDATE_URL)) {
                        $eventUrl = 'https://smithersevents.com' . $eventUrl;
                    }

                    $dateText = $node->filter('p.event-date')->count() ? trim($node->filter('p.event-date')->text()) : null;
                    $location = $node->filter('p.event-location')->count() ? trim($node->filter('p.event-location')->text()) : null;
                    
                    // Get description (last paragraph)
                    $description = null;
                    $paragraphs = $node->filter('p');
                    if ($paragraphs->count() > 2) {
                        $description = trim($paragraphs->last()->text());
                    }

                    // Extract data from hidden spans (more accurate)
                    $startDate = $node->filter('span._start')->count() ? trim($node->filter('span._start')->text()) : null;
                    $endDate = $node->filter('span._end')->count() ? trim($node->filter('span._end')->text()) : null;
                    $hiddenTitle = $node->filter('span._summary')->count() ? trim($node->filter('span._summary')->text()) : null;
                    $hiddenDescription = $node->filter('span._description')->count() ? trim($node->filter('span._description')->text()) : null;
                    $hiddenLocation = $node->filter('span._location')->count() ? trim($node->filter('span._location')->text()) : null;

                    // Get category
                    $category = null;
                    $categoryNode = $node->filter('.event-categories-icons .optional-screen-reader-text');
                    if ($categoryNode->count() > 0) {
                        $category = trim($categoryNode->text());
                    }

                    // Prefer hidden data over visible (more accurate)
                    $finalTitle = $hiddenTitle ?: $title;
                    $finalDescription = $hiddenDescription ?: $description;
                    $finalLocation = $hiddenLocation ?: $location;

                    // Parse dates (DD-MM-YYYY format)
                    $parsedStartDate = $this->parseDate($startDate);
                    $parsedEndDate = $endDate ? $this->parseDate($endDate) : null;

                    // Extract start and end times from date text
                    $times = $this->extractTimes($dateText);

                    // Generate source_id
                    $sourceId = $eventUrl ? 'smithers-' . md5($eventUrl) : 'smithers-' . md5($finalTitle . '-' . $parsedStartDate);

                    // Skip if missing required fields
                    if (!$finalTitle || !$parsedStartDate) {
                        if ($this->option('debug')) {
                            $this->newLine();
                            $this->warn("Skipping event #{$index}: Missing title or date");
                        }
                        $bar->advance();
                        return;
                    }

                    // Check if event exists
                    $existingEvent = Event::where('source_id', $sourceId)->first();

                    $eventData = [
                        'title' => $finalTitle,
                        'description' => $finalDescription,
                        'event_date' => $parsedStartDate,
                        'event_time' => $times['start_time'],
                        'end_date' => $parsedEndDate,
                        'end_time' => $times['end_time'],
                        'location' => $finalLocation,
                        'venue' => $finalLocation,
                        'organizer' => null,
                        'category' => $category,
                        'image_url' => null,
                        'external_url' => $eventUrl,
                        'source_id' => $sourceId,
                        'is_active' => true,
                        'scraped_at' => now(),
                    ];

                    if ($existingEvent) {
                        $existingEvent->update($eventData);
                        $stats['updated']++;
                        
                        if ($this->option('debug')) {
                            $this->newLine();
                            $this->line("✓ Updated: {$finalTitle}");
                            if ($times['start_time'] && $times['end_time']) {
                                $this->line("  Times: {$times['start_time']} - {$times['end_time']}");
                            }
                        }
                    } else {
                        Event::create($eventData);
                        $stats['new']++;
                        
                        if ($this->option('debug')) {
                            $this->newLine();
                            $this->line("✓ Created: {$finalTitle}");
                            if ($times['start_time'] && $times['end_time']) {
                                $this->line("  Times: {$times['start_time']} - {$times['end_time']}");
                            }
                        }
                    }

                } catch (Exception $e) {
                    $stats['errors']++;
                    if ($this->option('debug')) {
                        $this->newLine();
                        $this->error("Error processing event #{$index}: " . $e->getMessage());
                    }
                }

                $bar->advance();
            });

            $bar->finish();
            $this->newLine(2);

            // Display results
            $this->displayResults($stats);

            // Show database stats
            $dbCount = Event::count();
            $activeCount = Event::where('is_active', true)->count();
            $this->info("Total events in database: {$dbCount} ({$activeCount} active)");

            // Clean up old events
            if ($this->option('force') || $this->confirm('Do you want to deactivate past events?', true)) {
                $this->cleanupOldEvents();
            }

            $this->newLine();
            $this->info('✓ Scraping completed successfully!');

            return 0;

        } catch (Exception $e) {
            $this->error('Failed to scrape events: ' . $e->getMessage());
            if ($this->option('debug')) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }

    /**
     * Parse date from DD-MM-YYYY format
     */
    protected function parseDate($dateString)
    {
        if (!$dateString) {
            return null;
        }

        try {
            $dateString = trim($dateString);
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})/', $dateString, $matches)) {
                $date = Carbon::createFromFormat('d-m-Y', "{$matches[1]}-{$matches[2]}-{$matches[3]}");
                return $date->format('Y-m-d');
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Extract start and end times from date text
     */
    protected function extractTimes($dateText)
    {
        $result = [
            'start_time' => null,
            'end_time' => null,
        ];

        if (!$dateText) {
            return $result;
        }

        try {
            // Pattern for time range: "9:30am-10:15am" or "9:30 AM - 10:15 AM"
            if (preg_match('/(\d{1,2}:\d{2}\s*(?:am|pm))\s*-\s*(\d{1,2}:\d{2}\s*(?:am|pm))/i', $dateText, $matches)) {
                $startTime = Carbon::parse(trim($matches[1]));
                $result['start_time'] = $startTime->format('H:i:s');
                
                $endTime = Carbon::parse(trim($matches[2]));
                $result['end_time'] = $endTime->format('H:i:s');
            }
            // Pattern for single time
            elseif (preg_match('/(\d{1,2}:\d{2}\s*(?:am|pm))/i', $dateText, $matches)) {
                $time = Carbon::parse(trim($matches[1]));
                $result['start_time'] = $time->format('H:i:s');
            }
        } catch (Exception $e) {
            // Silently fail
        }

        return $result;
    }

    /**
     * Display results table
     */
    protected function displayResults($stats)
    {
        $this->info('═══════════════════════════════════════');
        $this->info('           SCRAPING RESULTS            ');
        $this->info('═══════════════════════════════════════');
        
        $this->table(
            ['Status', 'Count'],
            [
                ['New Events', $stats['new']],
                ['Updated Events', $stats['updated']],
                ['Errors', $stats['errors']],
            ]
        );

        $total = $stats['new'] + $stats['updated'];
        $this->info("Total events processed: {$total}");
    }

    /**
     * Deactivate past events
     */
    protected function cleanupOldEvents()
    {
        $this->info('Cleaning up past events...');

        $pastCount = Event::where('event_date', '<', now())
            ->where('is_active', true)
            ->count();

        if ($pastCount > 0) {
            Event::where('event_date', '<', now())
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $this->info("✓ Deactivated {$pastCount} past events");
        } else {
            $this->info('No past events to clean up');
        }
    }
}