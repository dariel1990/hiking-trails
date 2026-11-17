<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

/**
 * Light Markdown for admin-written descriptions: **bold**, *italic*, [links](https://…)
 * and lists. Raw HTML is stripped and unsafe link schemes (javascript:, data:) are
 * dropped, so the output is safe to echo unescaped. Single line breaks are kept.
 */
class RichText
{
    public static function toHtml(?string $markdown): ?HtmlString
    {
        if (blank($markdown)) {
            return null;
        }

        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10,
            'renderer' => ['soft_break' => "<br>\n"],
            'external_link' => [
                'internal_hosts' => [parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost'],
                'open_in_new_window' => true,
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ], [new ExternalLinkExtension]);

        return new HtmlString(trim($html));
    }

    /**
     * The text without any Markdown syntax, for places that can't show formatting
     * (map popups, meta descriptions).
     */
    public static function toPlainText(?string $markdown): string
    {
        $html = self::toHtml($markdown);

        if ($html === null) {
            return '';
        }

        $text = strip_tags(str_replace(['<br>', '</p>', '</li>'], ["\n", "\n\n", "\n"], $html->toHtml()));

        return trim(preg_replace("/\n{3,}/", "\n\n", html_entity_decode($text, ENT_QUOTES | ENT_HTML5)));
    }
}
