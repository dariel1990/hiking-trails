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

            // Extract time from the date text if present
            $eventTime = $this->extractTimeFromDateText($dateTimeText);

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
                'event_time' => $eventTime,
                'end_date' => $parsedEndDate,
                'end_time' => null,
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
     * Extract time from formatted date text
     *
     * @param string|null $dateText
     * @return string|null
     */
    protected function extractTimeFromDateText($dateText)
    {
        if (!$dateText) {
            return null;
        }

        try {
            // Look for time patterns like "9:30am" or "9:30 AM"
            if (preg_match('/(\d{1,2}:\d{2}\s*(?:am|pm))/i', $dateText, $matches)) {
                $timeStr = $matches[1];
                $time = Carbon::parse($timeStr);
                return $time->format('H:i:s');
            }

            return null;

        } catch (Exception $e) {
            Log::warning('Could not parse time from date text: ' . $dateText);
            return null;
        }
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