<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Services\EventImportService;
use App\Support\DeveloperAlert;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeSmithersEvents extends Command
{
    protected $signature = 'events:scrape
        {--debug : Show debug information}
        {--force : Force scrape and auto-cleanup}
        {--push : Also push the scraped events to the production import API}';

    protected $description = 'Scrape events from SmithersEvents.com';

    public function __construct(private EventImportService $importer)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting to scrape events from SmithersEvents.com...');
        $this->newLine();

        try {
            $url = rtrim((string) setting('events_scraper_base_url', 'https://smithersevents.com'), '/').'/';
            $html = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                ->get($url)
                ->throw()
                ->body();
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
                DeveloperAlert::send('events:scrape', "The scraper found zero events at {$url} — the site may be down or its markup may have changed. Existing events were left untouched.");

                return 1;
            }

            // Only clear existing data once we know the fetch succeeded, so a
            // broken scrape can never wipe the events table.
            if ($this->option('force')) {
                $this->info('Deleting all existing events...');
                Event::truncate();
                $this->info('✓ All events deleted.');
                $this->newLine();
            }

            $this->info('Found '.$eventNodes->count().' events. Processing...');
            $this->newLine();

            $bar = $this->output->createProgressBar($eventNodes->count());
            $bar->start();

            $payload = [];

            $eventNodes->each(function (Crawler $node, $index) use (&$stats, &$payload, $bar) {
                try {
                    // Extract data from visible elements
                    $title = $node->filter('h3 a')->count() ? trim($node->filter('h3 a')->text()) : null;
                    $eventUrl = $node->filter('h3 a')->count() ? $node->filter('h3 a')->attr('href') : null;

                    // Make URL absolute
                    if ($eventUrl && ! filter_var($eventUrl, FILTER_VALIDATE_URL)) {
                        $eventUrl = 'https://smithersevents.com'.$eventUrl;
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
                    $sourceId = $eventUrl ? 'smithers-'.md5($eventUrl) : 'smithers-'.md5($finalTitle.'-'.$parsedStartDate);

                    // Skip if missing required fields
                    if (! $finalTitle || ! $parsedStartDate) {
                        if ($this->option('debug')) {
                            $this->newLine();
                            $this->warn("Skipping event #{$index}: Missing title or date");
                        }
                        $bar->advance();

                        return;
                    }

                    $payload[] = [
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
                    ];

                    if ($this->option('debug')) {
                        $this->newLine();
                        $this->line("✓ Scraped: {$finalTitle}");
                        if ($times['start_time'] && $times['end_time']) {
                            $this->line("  Times: {$times['start_time']} - {$times['end_time']}");
                        }
                    }

                } catch (Exception $e) {
                    $stats['errors']++;
                    if ($this->option('debug')) {
                        $this->newLine();
                        $this->error("Error processing event #{$index}: ".$e->getMessage());
                    }
                }

                $bar->advance();
            });

            $bar->finish();
            $this->newLine(2);

            // Persist locally through the shared import service
            $importStats = $this->importer->import($payload);
            $stats['new'] = $importStats['new'];
            $stats['updated'] = $importStats['updated'];
            $stats['errors'] += $importStats['errors'];

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

            // Push the same payload to the production import API
            if ($this->option('push') && ! $this->pushToProduction($payload)) {
                return 1;
            }

            $this->newLine();
            $this->info('✓ Scraping completed successfully!');

            return 0;

        } catch (Exception $e) {
            $this->error('Failed to scrape events: '.$e->getMessage());
            if ($this->option('debug')) {
                $this->error($e->getTraceAsString());
            }

            DeveloperAlert::send('events:scrape', $e->getMessage());

            return 1;
        }
    }

    /**
     * Parse date from DD-MM-YYYY format
     */
    protected function parseDate($dateString)
    {
        if (! $dateString) {
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

        if (! $dateText) {
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

        $pastCount = $this->importer->deactivatePastEvents();

        if ($pastCount > 0) {
            $this->info("✓ Deactivated {$pastCount} past events");
        } else {
            $this->info('No past events to clean up');
        }
    }

    /**
     * POST the scraped events to the production import API.
     *
     * @param  array<int, array<string, mixed>>  $payload
     */
    protected function pushToProduction(array $payload): bool
    {
        $url = (string) config('services.events_import.push_url', '');
        $token = (string) config('services.events_import.token', '');

        if ($url === '' || $token === '') {
            $this->error('Push skipped: set EVENTS_PUSH_URL and EVENTS_IMPORT_TOKEN in .env first.');

            return false;
        }

        $this->newLine();
        $this->info('Pushing '.count($payload)." events to {$url}...");

        try {
            $response = Http::timeout(60)
                ->retry(3, 5000, throw: false)
                ->withToken($token)
                ->acceptJson()
                ->post($url, ['events' => $payload]);

            if (! $response->successful()) {
                throw new Exception("Import API responded with HTTP {$response->status()}: ".$response->body());
            }

            $remote = $response->json();
            $this->info(sprintf(
                '✓ Production import: %d new, %d updated, %d errors, %d past events deactivated',
                $remote['new'] ?? 0,
                $remote['updated'] ?? 0,
                $remote['errors'] ?? 0,
                $remote['deactivated'] ?? 0,
            ));

            return true;
        } catch (Exception $e) {
            $this->error('Push failed: '.$e->getMessage());
            DeveloperAlert::send('events:scrape --push', 'Pushing scraped events to production failed: '.$e->getMessage());

            return false;
        }
    }
}
