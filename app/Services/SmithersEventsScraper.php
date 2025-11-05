<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class SmithersEventsScraper
{
    protected $baseUrl = 'https://smithersevents.com';

    /**
     * Scrape events from SmithersEvents.com
     *
     * @return array
     */
    public function scrapeEvents()
    {
        try {
            Log::info('Starting to scrape events from SmithersEvents.com');

            // Fetch the HTML content
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ])
                ->get($this->baseUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch events page. Status: ' . $response->status());
            }

            $html = $response->body();

            // Parse the HTML
            $events = $this->parseHtml($html);

            Log::info('Successfully scraped ' . count($events) . ' unique events');

            return $events;

        } catch (Exception $e) {
            Log::error('Error scraping SmithersEvents.com: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse HTML content and extract event data
     *
     * @param string $html
     * @return array
     */
    protected function parseHtml($html)
    {
        $events = [];

        // Create a new Crawler instance
        $crawler = new Crawler($html);

        // Find all article elements with class "clearfix"
        $articles = $crawler->filter('article.clearfix');

        Log::info('Found ' . $articles->count() . ' event articles');

        $articles->each(function (Crawler $article, $index) use (&$events) {
            try {
                $eventData = $this->extractEventFromArticle($article, $index);
                
                if ($eventData && $this->validateEvent($eventData)) {
                    $events[] = $eventData;
                    Log::info("Extracted event #{$index}: {$eventData['title']}");
                } else {
                    Log::warning("Failed to validate event #{$index}");
                }

            } catch (Exception $e) {
                Log::warning("Error processing event #{$index}: " . $e->getMessage());
            }
        });

        return $events;
    }

    /**
     * Extract event data from an article element
     *
     * @param Crawler $article
     * @param int $index
     * @return array|null
     */
    protected function extractEventFromArticle(Crawler $article, $index)
    {
        try {
            // Find the addthisevent-drop link which contains hidden data spans
            $addThisLink = $article->filter('a.addthisevent-drop');
            
            if ($addThisLink->count() === 0) {
                Log::warning("No addthisevent-drop link found in article #{$index}");
                return null;
            }

            // Extract data from hidden spans using the helper method
            $startDate = $this->getSpanContent($addThisLink, '._start');
            $endDate = $this->getSpanContent($addThisLink, '._end');
            $title = $this->getSpanContent($addThisLink, '._summary');
            $description = $this->getSpanContent($addThisLink, '._description');
            $location = $this->getSpanContent($addThisLink, '._location');
            $allDayEvent = $this->getSpanContent($addThisLink, '._all_day_event');

            // Get event URL from h3 > a
            $eventUrl = null;
            $eventLink = $article->filter('h3 a');
            if ($eventLink->count() > 0) {
                $eventUrl = $eventLink->attr('href');
                if (!filter_var($eventUrl, FILTER_VALIDATE_URL)) {
                    $eventUrl = $this->baseUrl . $eventUrl;
                }
            }

            // Get category from icon section
            $category = null;
            $categoryNode = $article->filter('.event-categories-icons .optional-screen-reader-text');
            if ($categoryNode->count() > 0) {
                $category = trim($categoryNode->text());
            }

            // Get formatted date/time text
            $dateTimeText = null;
            $eventDateNode = $article->filter('p.event-date');
            if ($eventDateNode->count() > 0) {
                $dateTimeText = trim($eventDateNode->text());
            }

            // Extract start and end times from the date text
            $times = $this->extractTimesFromDateText($dateTimeText);

            // Parse dates
            $parsedStartDate = $this->parseDate($startDate);
            $parsedEndDate = $endDate ? $this->parseDate($endDate) : null;

            // Generate unique source ID from URL
            if ($eventUrl) {
                $sourceId = 'smithers-' . md5($eventUrl);
            } else {
                $sourceId = 'smithers-' . md5($title . '-' . $parsedStartDate);
            }

            return [
                'title' => trim($title),
                'description' => trim($description),
                'event_date' => $parsedStartDate,
                'event_time' => $times['start_time'],
                'end_date' => $parsedEndDate,
                'end_time' => $times['end_time'],
                'location' => trim($location),
                'venue' => trim($location),
                'organizer' => null,
                'category' => $category,
                'image_url' => null,
                'external_url' => $eventUrl,
                'source_id' => $sourceId,
                'is_active' => true,
                'scraped_at' => now(),
            ];

        } catch (Exception $e) {
            Log::error("Error extracting event from article: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get content from a span with specific class inside a crawler
     *
     * @param Crawler $crawler
     * @param string $selector
     * @return string|null
     */
    protected function getSpanContent(Crawler $crawler, $selector)
    {
        try {
            $span = $crawler->filter($selector);
            if ($span->count() > 0) {
                return trim($span->text());
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Extract both start and end times from formatted date text
     *
     * @param string|null $dateText
     * @return array ['start_time' => string|null, 'end_time' => string|null]
     */
    protected function extractTimesFromDateText($dateText)
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
                // Found start and end time
                $startTimeStr = trim($matches[1]);
                $endTimeStr = trim($matches[2]);
                
                $startTime = Carbon::parse($startTimeStr);
                $result['start_time'] = $startTime->format('H:i:s');
                
                $endTime = Carbon::parse($endTimeStr);
                $result['end_time'] = $endTime->format('H:i:s');
                
                Log::info("Extracted times: Start={$result['start_time']}, End={$result['end_time']}");
            }
            // Pattern for single time: "9:30am" or "9:30 AM"
            elseif (preg_match('/(\d{1,2}:\d{2}\s*(?:am|pm))/i', $dateText, $matches)) {
                $timeStr = trim($matches[1]);
                $time = Carbon::parse($timeStr);
                $result['start_time'] = $time->format('H:i:s');
                
                Log::info("Extracted single time: {$result['start_time']}");
            }

        } catch (Exception $e) {
            Log::warning('Could not parse times from date text: ' . $dateText . ' - ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Parse date string from DD-MM-YYYY format
     *
     * @param string|null $dateString
     * @return string|null
     */
    protected function parseDate($dateString)
    {
        if (!$dateString) {
            return null;
        }

        try {
            // Remove any extra whitespace
            $dateString = trim($dateString);

            // Parse DD-MM-YYYY format
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})/', $dateString, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                
                $date = Carbon::createFromFormat('d-m-Y', "$day-$month-$year");
                return $date->format('Y-m-d');
            }

            return null;

        } catch (Exception $e) {
            Log::warning('Could not parse date: ' . $dateString);
            return null;
        }
    }

    /**
     * Validate event data
     *
     * @param array $event
     * @return bool
     */
    protected function validateEvent($event)
    {
        return !empty($event['title']) && 
               !empty($event['event_date']) &&
               !empty($event['source_id']);
    }
}