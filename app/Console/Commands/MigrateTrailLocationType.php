<?php

namespace App\Console\Commands;

use App\Models\Trail;
use Illuminate\Console\Command;

class MigrateTrailLocationType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trails:migrate-location-type 
                            {--dry-run : Run without making changes}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing trails to set location_type and geometry_type based on their data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔍 Analyzing existing trails...');
        $this->newLine();

        // Fetch all trails that don't have location_type set
        $trails = Trail::whereNull('location_type')
            ->orWhere('location_type', '')
            ->get();

        if ($trails->isEmpty()) {
            $this->info('✅ No trails found that need migration.');
            $this->info('All trails already have location_type set.');

            return Command::SUCCESS;
        }

        $this->info("Found {$trails->count()} trail(s) that need migration.");
        $this->newLine();

        // Analyze trails
        $trailCount = 0;
        $fishingLakeCount = 0;

        foreach ($trails as $trail) {
            // Determine if it's a trail or fishing lake based on existing data
            // If it has route coordinates (multiple points), it's a trail
            // If it has only a single point, it could be a fishing lake
            $routeCoords = $trail->route_coordinates;
            $coordCount = is_array($routeCoords) ? count($routeCoords) : 0;

            // For now, we'll assume all existing records are trails
            // since fishing lakes are a new feature
            $trail->temp_location_type = 'trail';
            $trail->temp_geometry_type = 'linestring';
            $trailCount++;
        }

        // Display summary
        $this->table(
            ['Type', 'Count'],
            [
                ['Trails', $trailCount],
                ['Fishing Lakes', $fishingLakeCount],
            ]
        );

        $this->newLine();

        if ($isDryRun) {
            $this->warn('🔸 DRY RUN MODE - No changes will be made.');
            $this->newLine();

            foreach ($trails as $trail) {
                $this->line("  [{$trail->id}] {$trail->name}");
                $this->line("    → location_type: {$trail->temp_location_type}");
                $this->line("    → geometry_type: {$trail->temp_geometry_type}");
                $this->newLine();
            }

            $this->info('To apply these changes, run without --dry-run');

            return Command::SUCCESS;
        }

        // Confirm before proceeding
        if (! $force) {
            if (! $this->confirm('Do you want to proceed with the migration?')) {
                $this->info('Migration cancelled.');

                return Command::CANCELLED;
            }
        }

        // Perform migration
        $this->info('🚀 Migrating trails...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar($trails->count());
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($trails as $trail) {
            try {
                $trail->location_type = 'trail';
                $trail->geometry_type = 'linestring';

                // Set default values for trail-specific fields if they're null
                if ($trail->difficulty_level === null) {
                    $trail->difficulty_level = 3; // Default to moderate
                }
                if ($trail->trail_type === null) {
                    $trail->trail_type = 'loop'; // Default to loop
                }

                $trail->save();
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $this->newLine();
                $this->error("  Error updating trail [{$trail->id}] {$trail->name}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        if ($successCount > 0) {
            $this->info("✅ Successfully migrated {$successCount} trail(s).");
        }

        if ($errorCount > 0) {
            $this->error("❌ Failed to migrate {$errorCount} trail(s). Check the errors above.");

            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('🎉 Migration completed successfully!');

        return Command::SUCCESS;
    }
}
