<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Facility;
use App\Models\Tour;
use App\Models\Town;
use App\Models\Trail;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AssignTowns extends Command
{
    protected $signature = 'towns:assign
                            {--town=* : Limit writes to these towns (slug or name, repeatable)}
                            {--dry-run : Report what would change without writing anything}
                            {--force : Reassign records that already belong to a town}';

    protected $description = 'Assign trails, businesses, facilities and tours to their nearest town';

    /**
     * @var Collection<int, Town>
     */
    private Collection $towns;

    /**
     * Tally of assignments made, keyed by town name.
     *
     * @var array<string, array<string, int>>
     */
    private array $tally = [];

    /**
     * Town ids this run is allowed to write, or null for every town.
     *
     * Nearest-town matching always runs against every active town even when
     * this is set, so a scoped run can never claim a record that actually
     * belongs to a neighbour.
     *
     * @var list<int>|null
     */
    private ?array $scope = null;

    private int $unassigned = 0;

    private int $outOfScope = 0;

    public function handle(): int
    {
        $this->towns = Town::active()->get();

        if ($this->towns->isEmpty()) {
            $this->error('No active towns found. Run: php artisan db:seed --class=TownSeeder');

            return self::FAILURE;
        }

        if (! $this->resolveScope()) {
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run - nothing will be written.');
        }

        $this->assignByCoordinates(Trail::query(), 'trails', 'start_latitude', 'start_longitude', $dryRun);
        $this->assignByCoordinates(Business::query(), 'businesses', 'latitude', 'longitude', $dryRun);
        $this->assignByCoordinates(Facility::query(), 'facilities', 'latitude', 'longitude', $dryRun);
        $this->assignTours($dryRun);

        $this->renderSummary();

        return self::SUCCESS;
    }

    /**
     * Turn any --town options into a list of ids to write.
     *
     * Accepts a slug or a name so both `--town=burns-lake-bc` and
     * `--town="Burns Lake"` work. Returns false when a value matches nothing,
     * so a typo stops the run instead of silently assigning nothing.
     */
    private function resolveScope(): bool
    {
        $requested = (array) $this->option('town');

        if ($requested === []) {
            return true;
        }

        $scope = [];

        foreach ($requested as $value) {
            $town = $this->towns->first(fn (Town $town) => $town->slug === $value
                || strcasecmp($town->name, $value) === 0);

            if (! $town) {
                $this->error("No active town matches [{$value}].");
                $this->line('Available: '.$this->towns->pluck('slug')->implode(', '));

                return false;
            }

            $scope[] = $town->id;
        }

        $this->scope = array_values(array_unique($scope));

        $names = $this->towns->whereIn('id', $this->scope)->pluck('name')->implode(', ');
        $this->info("Scoped to: {$names}");
        $this->line('Records nearer to another town are left alone.');
        $this->newLine();

        return true;
    }

    /**
     * Whether this run is allowed to write the given town.
     */
    private function inScope(Town $town): bool
    {
        return $this->scope === null || in_array($town->id, $this->scope, true);
    }

    /**
     * Assign every record with usable coordinates to the nearest town that
     * claims it, i.e. whose distance is within that town's own radius.
     *
     * @param  Builder<covariant Model>  $query
     */
    private function assignByCoordinates($query, string $label, string $latColumn, string $lngColumn, bool $dryRun): void
    {
        $query->whereNotNull($latColumn)->whereNotNull($lngColumn);

        if (! $this->option('force')) {
            $query->whereNull('town_id');
        }

        $query->chunkById(200, function (Collection $records) use ($label, $latColumn, $lngColumn, $dryRun) {
            foreach ($records as $record) {
                $town = $this->nearestTown((float) $record->{$latColumn}, (float) $record->{$lngColumn});

                if ($town === null) {
                    $this->unassigned++;

                    continue;
                }

                if (! $this->inScope($town)) {
                    $this->outOfScope++;

                    continue;
                }

                $this->tally[$town->name][$label] = ($this->tally[$town->name][$label] ?? 0) + 1;

                if (! $dryRun) {
                    $record->forceFill(['town_id' => $town->id])->saveQuietly();
                }
            }
        });
    }

    /**
     * A tour belongs to the town most of its stops fall in. Stop coordinates
     * resolve facility, then feature, then trail - the same precedence the
     * /api/tours endpoint uses.
     */
    private function assignTours(bool $dryRun): void
    {
        $query = Tour::query()->with('stops.trail', 'stops.feature', 'stops.facility');

        if (! $this->option('force')) {
            $query->whereNull('town_id');
        }

        foreach ($query->get() as $tour) {
            $votes = [];

            foreach ($tour->stops as $stop) {
                $coordinates = $this->stopCoordinates($stop);

                if ($coordinates === null) {
                    continue;
                }

                $town = $this->nearestTown($coordinates[0], $coordinates[1]);

                if ($town !== null) {
                    $votes[$town->id] = ($votes[$town->id] ?? 0) + 1;
                }
            }

            if ($votes === []) {
                $this->unassigned++;

                continue;
            }

            arsort($votes);
            $townId = array_key_first($votes);
            $town = $this->towns->firstWhere('id', $townId);

            if (! $this->inScope($town)) {
                $this->outOfScope++;

                continue;
            }

            $this->tally[$town->name]['tours'] = ($this->tally[$town->name]['tours'] ?? 0) + 1;

            if (! $dryRun) {
                $tour->forceFill(['town_id' => $townId])->saveQuietly();
            }
        }
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    private function stopCoordinates(Model $stop): ?array
    {
        if ($stop->facility && $stop->facility->latitude !== null) {
            return [(float) $stop->facility->latitude, (float) $stop->facility->longitude];
        }

        if ($stop->feature && is_array($stop->feature->coordinates) && isset($stop->feature->coordinates[0], $stop->feature->coordinates[1])) {
            return [(float) $stop->feature->coordinates[0], (float) $stop->feature->coordinates[1]];
        }

        if ($stop->trail && $stop->trail->start_latitude !== null) {
            return [(float) $stop->trail->start_latitude, (float) $stop->trail->start_longitude];
        }

        return null;
    }

    /**
     * The closest town that is willing to claim this point, or null when the
     * point falls outside every town's radius.
     */
    private function nearestTown(float $latitude, float $longitude): ?Town
    {
        $nearest = null;
        $nearestDistance = null;

        foreach ($this->towns as $town) {
            $distance = $town->distanceTo($latitude, $longitude);

            if ($distance > $town->radius_km) {
                continue;
            }

            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearest = $town;
                $nearestDistance = $distance;
            }
        }

        return $nearest;
    }

    private function renderSummary(): void
    {
        $rows = [];

        $towns = $this->scope === null
            ? $this->towns
            : $this->towns->whereIn('id', $this->scope);

        foreach ($towns as $town) {
            $counts = $this->tally[$town->name] ?? [];

            $rows[] = [
                $town->name,
                $counts['trails'] ?? 0,
                $counts['businesses'] ?? 0,
                $counts['facilities'] ?? 0,
                $counts['tours'] ?? 0,
            ];
        }

        $this->newLine();
        $this->table(['Town', 'Trails', 'Businesses', 'Facilities', 'Tours'], $rows);

        if ($this->unassigned > 0) {
            $this->warn("{$this->unassigned} record(s) fell outside every town radius and were left unassigned.");
        }

        if ($this->outOfScope > 0) {
            $this->line("{$this->outOfScope} record(s) belong to a town outside this run's --town scope and were skipped.");
        }
    }
}
