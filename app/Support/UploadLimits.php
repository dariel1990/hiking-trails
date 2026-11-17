<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * The PHP request-size limits that govern uploads, exposed to the frontend
 * (layout meta tags) so oversized uploads are blocked before they're sent,
 * and to the PostTooLargeException handler for a readable error message.
 */
class UploadLimits
{
    /**
     * Largest request body PHP will accept (post_max_size), in bytes. 0 means unlimited.
     */
    public static function maxPostBytes(): int
    {
        return self::parseIniSize((string) ini_get('post_max_size'));
    }

    /**
     * Largest single uploaded file PHP will accept, in bytes.
     */
    public static function maxFileBytes(): int
    {
        return (int) UploadedFile::getMaxFilesize();
    }

    /**
     * Human-readable size, e.g. "20 MB".
     */
    public static function format(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return rtrim(rtrim(number_format($bytes / (1024 * 1024), 1), '0'), '.').' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024).' KB';
        }

        return $bytes.' bytes';
    }

    public static function tooLargeMessage(): string
    {
        $max = self::maxPostBytes();

        return $max > 0
            ? 'The upload is too large. The total size of the files you send at once must be under '.self::format($max).'.'
            : 'The upload is too large.';
    }

    public static function parseIniSize(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
