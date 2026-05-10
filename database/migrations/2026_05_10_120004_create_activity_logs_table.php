<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the activity_logs table.
 *
 * Tracks all significant user actions in the system for auditing.
 * Uses a polymorphic-style approach with model_type/model_id
 * and stores change diffs as JSON.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);           // e.g., 'created', 'updated', 'archived', 'deleted'
            $table->string('model_type')->nullable(); // e.g., 'App\Models\Research'
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('changes')->nullable();     // Before/after diff
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Indexes for efficient querying
            $table->index('user_id');
            $table->index('action');
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
