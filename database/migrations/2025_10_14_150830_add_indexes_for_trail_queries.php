<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('trails', function (Blueprint $table) {
            // Index for GPX-related queries
            $table->index('gpx_uploaded_at');
            $table->index('data_source');
            
            // Index for common queries
            $table->index(['status', 'is_featured']);
            $table->index('difficulty_level');
        });
    }

    public function down()
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->dropIndex(['gpx_uploaded_at']);
            $table->dropIndex(['data_source']);
            $table->dropIndex(['status', 'is_featured']);
            $table->dropIndex(['difficulty_level']);
        });
    }
};