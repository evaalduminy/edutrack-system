<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Archive Record Model
 *
 * Represents an archived research entry with a unique archive number.
 * Contains metadata about the archiving process and audit information.
 *
 * @property int $id
 * @property int $research_id
 * @property string $archive_number
 * @property int $archived_by
 * @property string|null $notes
 * @property array|null $archive_metadata
 * @property \Carbon\Carbon $archived_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ArchiveRecord extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'research_id',
        'archive_number',
        'archived_by',
        'notes',
        'archive_metadata',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'archive_metadata' => 'array',
            'archived_at'      => 'datetime',
        ];
    }

    // ─── Relationships ───

    public function research(): BelongsTo
    {
        return $this->belongsTo(Research::class);
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}
