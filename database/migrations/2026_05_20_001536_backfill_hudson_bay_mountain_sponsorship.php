<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Move the previously hardcoded Hudson Bay Mountain sponsorship (Phil Bernier)
     * into the new trail_network_sponsors table so behaviour is unchanged after migration.
     */
    public function up(): void
    {
        $network = DB::table('trail_networks')
            ->where('slug', 'hudson-bay-mountain-ski-ride-smithers')
            ->first();

        if (! $network) {
            return;
        }

        $alreadyBackfilled = DB::table('trail_network_sponsors')
            ->where('trail_network_id', $network->id)
            ->where('name', 'Phil Bernier')
            ->exists();

        if ($alreadyBackfilled) {
            return;
        }

        $logoPath = null;
        $sourceLogo = public_path('images/phil-bernier-realtor-logo.png');
        if (is_file($sourceLogo)) {
            $destination = 'trail-network-sponsors/phil-bernier-realtor-logo.png';
            if (! Storage::disk('public')->exists($destination)) {
                Storage::disk('public')->put($destination, file_get_contents($sourceLogo));
            }
            $logoPath = $destination;
        }

        $now = now();

        DB::table('trail_network_sponsors')->insert([
            'trail_network_id' => $network->id,
            'name' => 'Phil Bernier',
            'tagline' => 'REALTOR®',
            'logo' => $logoPath,
            'url' => 'https://bvliving.ca/',
            'welcome_message' => 'Welcome to Hudson Bay Mountain!',
            'banner_text' => 'Trail maps proudly sponsored by',
            'cta_text' => 'Visit BVLiving.ca',
            'sort_order' => 0,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $network = DB::table('trail_networks')
            ->where('slug', 'hudson-bay-mountain-ski-ride-smithers')
            ->first();

        if (! $network) {
            return;
        }

        DB::table('trail_network_sponsors')
            ->where('trail_network_id', $network->id)
            ->where('name', 'Phil Bernier')
            ->delete();
    }
};
