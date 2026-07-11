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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type');
            $table->string('recipient_email')->index();
            $table->nullableMorphs('notifiable');
            $table->string('subject')->nullable();
            $table->string('status')->index();
            $table->text('error')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamp('resent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
