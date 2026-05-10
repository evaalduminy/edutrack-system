<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Research Model
 *
 * Represents an academic research entry in the system.
 * Enhanced with AI metadata, file hashing, and archive support.
 *
 * @property int $id
 * @property string $title
 * @property string|null $abstract
 * @property string|null $file_path
 * @property string|null $file_hash_sha256
 * @property string $status
 * @property array|null $ai_metadata
 * @property int $researcher_id
 * @property int|null $supervisor_id
 * @property int $department_id
 * @property \Carbon\Carbon|null $archived_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Research extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'research';

    /**
     * Mass Assignment Protection: only these fields can be mass-assigned.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'abstract',
        'file_path',
        'file_hash_sha256',
        'status',
        'ai_metadata',
        'researcher_id',
        'supervisor_id',
        'department_id',
        'archived_at',
    ];

    /**
     * Attribute casting for proper type handling.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ai_metadata' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    // ─── Relationships ───

    public function researcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'researcher_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ResearchDocument::class);
    }

    public function archiveRecord(): HasOne
    {
        return $this->hasOne(ArchiveRecord::class);
    }

    // ─── Scopes ───

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    // ─── Accessors ───

    /**
     * Check if the research has been archived.
     */
    public function getIsArchivedAttribute(): bool
    {
        return $this->archived_at !== null;
    }
}
