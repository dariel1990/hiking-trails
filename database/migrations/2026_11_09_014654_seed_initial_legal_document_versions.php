<?php

use Database\Seeders\LegalDocumentSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new LegalDocumentSeeder)->run();
    }

    public function down(): void
    {
        //
    }
};
