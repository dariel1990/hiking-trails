<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trails', function (Blueprint $table) {
            /**
             * The "recently added" row on the homepage and the API's
             * ?sort=newest both order by this, and nothing indexed it before.
             */
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
