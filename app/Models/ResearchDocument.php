<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Research Document Model
 *
 * Represents an individual file/document attached to a research entry.
 * Stores file metadata, SHA-256 hash for integrity, and AI-extracted metadata.
 *
 * @property int $id
 * @property int $research_id
 * @property string $original_name
 * @property string $stored_path
 * @property string|null $mime_type
 * @property int $file_size
 * @property string|null $sha256_hash
 * @property string|null $qr_code_path
 * @property array|null $extracted_metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ResearchDocument extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'research_id',
        'original_name',
        'stored_path',
        'mime_type',
        'file_size',
        'sha256_hash',
        'qr_code_path',
        'extracted_metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'extracted_metadata' => 'array',
            'file_size'          => 'integer',
        ];
    }

    // ─── Relationships ───

    public function research(): BelongsTo
    {
        return $this->belongsTo(Research::class);
    }

    // ─── Accessors ───

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
