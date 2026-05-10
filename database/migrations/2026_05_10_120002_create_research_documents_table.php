<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the research_documents table.
 *
 * Stores individual document files associated with a research entry.
 * Each document has its own SHA-256 hash for integrity and duplicate detection,
 * optional QR code path, and extracted metadata from AI processing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_id')->constrained('research')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('sha256_hash', 64)->nullable()->unique();
            $table->string('qr_code_path')->nullable();
            $table->json('extracted_metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('research_id');
            $table->index('sha256_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_documents');
    }
};
