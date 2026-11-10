<?php

namespace App\Support;

class LineDiff
{
    public const EQUAL = 'equal';

    public const ADDED = 'added';

    public const REMOVED = 'removed';

    /**
     * Above this many comparisons the changed region is shown as a full
     * replacement, which keeps the longest-common-subsequence table bounded.
     */
    private const MAX_COMPARISONS = 4_000_000;

    /**
     * @return list<array{type: string, line: string}>
     */
    public static function compare(string $old, string $new): array
    {
        $oldLines = self::lines($old);
        $newLines = self::lines($new);

        $prefixLength = 0;
        while ($prefixLength < count($oldLines) && $prefixLength < count($newLines) && $oldLines[$prefixLength] === $newLines[$prefixLength]) {
            $prefixLength++;
        }

        $suffixLength = 0;
        while (
            $suffixLength < count($oldLines) - $prefixLength
            && $suffixLength < count($newLines) - $prefixLength
            && $oldLines[count($oldLines) - 1 - $suffixLength] === $newLines[count($newLines) - 1 - $suffixLength]
        ) {
            $suffixLength++;
        }

        $changedOld = array_slice($oldLines, $prefixLength, count($oldLines) - $prefixLength - $suffixLength);
        $changedNew = array_slice($newLines, $prefixLength, count($newLines) - $prefixLength - $suffixLength);

        return [
            ...self::rows(self::EQUAL, array_slice($oldLines, 0, $prefixLength)),
            ...self::diffChangedRegion($changedOld, $changedNew),
            ...self::rows(self::EQUAL, array_slice($oldLines, count($oldLines) - $suffixLength)),
        ];
    }

    /**
     * @param  list<string>  $old
     * @param  list<string>  $new
     * @return list<array{type: string, line: string}>
     */
    private static function diffChangedRegion(array $old, array $new): array
    {
        $oldCount = count($old);
        $newCount = count($new);

        if ($oldCount === 0 || $newCount === 0 || $oldCount * $newCount > self::MAX_COMPARISONS) {
            return [...self::rows(self::REMOVED, $old), ...self::rows(self::ADDED, $new)];
        }

        $lengths = array_fill(0, $oldCount + 1, array_fill(0, $newCount + 1, 0));

        for ($i = $oldCount - 1; $i >= 0; $i--) {
            for ($j = $newCount - 1; $j >= 0; $j--) {
                $lengths[$i][$j] = $old[$i] === $new[$j]
                    ? $lengths[$i + 1][$j + 1] + 1
                    : max($lengths[$i + 1][$j], $lengths[$i][$j + 1]);
            }
        }

        $rows = [];
        $i = 0;
        $j = 0;

        while ($i < $oldCount && $j < $newCount) {
            if ($old[$i] === $new[$j]) {
                $rows[] = ['type' => self::EQUAL, 'line' => $old[$i]];
                $i++;
                $j++;
            } elseif ($lengths[$i + 1][$j] >= $lengths[$i][$j + 1]) {
                $rows[] = ['type' => self::REMOVED, 'line' => $old[$i]];
                $i++;
            } else {
                $rows[] = ['type' => self::ADDED, 'line' => $new[$j]];
                $j++;
            }
        }

        return [
            ...$rows,
            ...self::rows(self::REMOVED, array_slice($old, $i)),
            ...self::rows(self::ADDED, array_slice($new, $j)),
        ];
    }

    /**
     * @return list<string>
     */
    private static function lines(string $text): array
    {
        if ($text === '') {
            return [];
        }

        return explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
    }

    /**
     * @param  list<string>  $lines
     * @return list<array{type: string, line: string}>
     */
    private static function rows(string $type, array $lines): array
    {
        return array_map(fn (string $line): array => ['type' => $type, 'line' => $line], $lines);
    }
}
