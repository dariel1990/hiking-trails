<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TrailPhoto extends Model
{
    /** @use HasFactory<\Database\Factories\TrailPhotoFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'trail_id',
        'image_path',
        'thumbnail_path',
        'caption',
        'name',
        'email',
        'submitter_ip',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $hidden = [
        'email',
        'submitter_ip',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_path) {
            return Storage::url($this->thumbnail_path);
        }

        return $this->image_url;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Transition this photo to a new status and record the audit fields.
     * Moving to "rejected" also deletes the stored files (the row remains
     * for auditing).
     */
    public function setStatus(string $status, ?User $reviewer = null): void
    {
        $this->status = $status;
        $this->reviewed_by = $reviewer?->id;
        $this->reviewed_at = now();
        $this->save();

        if ($status === self::STATUS_REJECTED) {
            $this->deleteStoredFiles();
            $this->forceFill([
                'image_path' => null,
                'thumbnail_path' => null,
            ])->save();
        }
    }

    protected static function booted(): void
    {
        static::deleting(function (TrailPhoto $photo) {
            $photo->deleteStoredFiles();
        });
    }

    public function deleteStoredFiles(): void
    {
        $disk = Storage::disk('public');

        if ($this->image_path && $disk->exists($this->image_path)) {
            $disk->delete($this->image_path);
        }

        if ($this->thumbnail_path && $disk->exists($this->thumbnail_path)) {
            $disk->delete($this->thumbnail_path);
        }
    }
}
