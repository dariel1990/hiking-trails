<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trail_media', function (Blueprint $table) {
            // Make these fields properly nullable since external videos won't have them
            $table->string('filename')->nullable()->change();
            $table->string('original_name')->nullable()->change();
            $table->string('storage_path', 500)->nullable()->change();
            $table->string('mime_type', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('trail_media', function (Blueprint $table) {
            $table->string('filename')->nullable(false)->change();
            $table->string('original_name')->nullable(false)->change();
            $table->string('storage_path', 500)->nullable(false)->change();
            $table->string('mime_type', 100)->nullable(false)->change();
        });
    }
};