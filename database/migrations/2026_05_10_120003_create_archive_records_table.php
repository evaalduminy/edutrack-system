<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the archive_records table.
 *
 * Stores the archiving records for approved research.
 * Each record has a unique archive number, metadata about the archiving process,
 * and is linked to the user who performed the archiving.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archive_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_id')->constrained('research')->cascadeOnDelete();
            $table->string('archive_number')->unique();
            $table->foreignId('archived_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->json('archive_metadata')->nullable();
            $table->timestamp('archived_at');
            $table->timestamps();

            // Indexes
            $table->index('archive_number');
            $table->index('archived_by');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_records');
    }
};
