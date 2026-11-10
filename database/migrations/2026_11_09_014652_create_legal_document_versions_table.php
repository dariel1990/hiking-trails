<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_document_versions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->unsignedInteger('version')->nullable();
            $table->longText('content');
            $table->text('change_summary')->nullable();
            $table->boolean('is_material')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['type', 'version']);
            $table->index(['type', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_document_versions');
    }
};
