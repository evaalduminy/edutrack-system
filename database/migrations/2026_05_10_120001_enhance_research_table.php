<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enhance the research table with archiving and AI metadata support.
 *
 * Adds:
 * - file_hash_sha256: SHA-256 hash for duplicate detection and integrity
 * - ai_metadata: JSONB column for NLP-extracted metadata (keywords, entities, summary)
 * - archived_at: Timestamp for when the research was archived
 * - Indexes for search performance
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research', function (Blueprint $table) {
            $table->string('file_hash_sha256', 64)->nullable()->after('file_path');
            $table->json('ai_metadata')->nullable()->after('status');
            $table->timestamp('archived_at')->nullable()->after('department_id');

            // Performance indexes
            $table->index('file_hash_sha256');
            $table->index('status');
            $table->index('archived_at');
            $table->index(['department_id', 'status']);
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::table('research', function (Blueprint $table) {
            $table->dropIndex(['file_hash_sha256']);
            $table->dropIndex(['status']);
            $table->dropIndex(['archived_at']);
            $table->dropIndex(['department_id', 'status']);
            $table->dropIndex(['title']);

            $table->dropColumn(['file_hash_sha256', 'ai_metadata', 'archived_at']);
        });
    }
};
