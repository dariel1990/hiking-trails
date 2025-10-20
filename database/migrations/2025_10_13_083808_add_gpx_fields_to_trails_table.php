<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->longText('gpx_raw_data')->nullable()->after('gpx_file_path');
            $table->decimal('gpx_calculated_distance', 8, 2)->nullable()->after('gpx_raw_data');
            $table->integer('gpx_calculated_elevation')->nullable()->after('gpx_calculated_distance');
            $table->decimal('gpx_calculated_time', 4, 2)->nullable()->after('gpx_calculated_elevation');
            $table->enum('data_source', ['gpx', 'manual', 'mixed'])->default('manual')->after('gpx_calculated_time');
            $table->timestamp('gpx_uploaded_at')->nullable()->after('data_source');
        });
    }

    public function down()
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->dropColumn([
                'gpx_raw_data',
                'gpx_calculated_distance',
                'gpx_calculated_elevation',
                'gpx_calculated_time',
                'data_source',
                'gpx_uploaded_at'
            ]);
        });
    }
};