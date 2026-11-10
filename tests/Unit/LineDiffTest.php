<?php

namespace Tests\Unit;

use App\Support\LineDiff;
use PHPUnit\Framework\TestCase;

class LineDiffTest extends TestCase
{
    /**
     * @param  list<array{type: string, line: string}>  $rows
     * @return list<string>
     */
    private function flatten(array $rows): array
    {
        return array_map(fn (array $row): string => $row['type'].':'.$row['line'], $rows);
    }

    public function test_identical_text_is_all_equal(): void
    {
        $this->assertSame(['equal:a', 'equal:b'], $this->flatten(LineDiff::compare("a\nb", "a\nb")));
    }

    public function test_added_line_in_the_middle(): void
    {
        $this->assertSame(
            ['equal:a', 'added:new', 'equal:b'],
            $this->flatten(LineDiff::compare("a\nb", "a\nnew\nb")),
        );
    }

    public function test_removed_line_in_the_middle(): void
    {
        $this->assertSame(
            ['equal:a', 'removed:old', 'equal:b'],
            $this->flatten(LineDiff::compare("a\nold\nb", "a\nb")),
        );
    }

    public function test_changed_line_is_a_removal_and_an_addition(): void
    {
        $this->assertSame(
            ['equal:a', 'removed:before', 'added:after', 'equal:c'],
            $this->flatten(LineDiff::compare("a\nbefore\nc", "a\nafter\nc")),
        );
    }

    public function test_unchanged_lines_between_separate_changes_stay_equal(): void
    {
        $this->assertSame(
            ['removed:x', 'added:y', 'equal:keep', 'removed:p', 'added:q'],
            $this->flatten(LineDiff::compare("x\nkeep\np", "y\nkeep\nq")),
        );
    }

    public function test_empty_old_text_is_all_added(): void
    {
        $this->assertSame(['added:a', 'added:b'], $this->flatten(LineDiff::compare('', "a\nb")));
    }

    public function test_empty_new_text_is_all_removed(): void
    {
        $this->assertSame(['removed:a'], $this->flatten(LineDiff::compare('a', '')));
    }

    public function test_both_empty_returns_no_rows(): void
    {
        $this->assertSame([], LineDiff::compare('', ''));
    }

    public function test_windows_line_endings_do_not_count_as_changes(): void
    {
        $this->assertSame(['equal:a', 'equal:b'], $this->flatten(LineDiff::compare("a\r\nb", "a\nb")));
    }
}
