<?php

namespace App\Models;

use App\Services\LegalDocumentService;
use Database\Factories\LegalDocumentVersionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use LogicException;

class LegalDocumentVersion extends Model
{
    /** @use HasFactory<LegalDocumentVersionFactory> */
    use HasFactory;

    public const TERMS = 'terms';

    public const PRIVACY = 'privacy';

    /**
     * @var array<string, string>
     */
    public const TITLES = [
        self::TERMS => 'Terms & Conditions',
        self::PRIVACY => 'Privacy Policy',
    ];

    /**
     * @var array<string, string>
     */
    public const PUBLIC_ROUTES = [
        self::TERMS => 'terms',
        self::PRIVACY => 'privacy-policy',
    ];

    protected $fillable = [
        'type',
        'version',
        'content',
        'change_summary',
        'is_material',
        'created_by',
        'published_by',
        'published_at',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $document): void {
            if ($document->getOriginal('published_at') !== null) {
                throw new LogicException('Published legal document versions cannot be changed.');
            }
        });

        static::deleting(function (self $document): void {
            if ($document->getOriginal('published_at') !== null) {
                throw new LogicException('Published legal document versions cannot be deleted.');
            }
        });

        static::saved(fn () => Cache::forget(LegalDocumentService::CACHE_KEY));
        static::deleted(fn () => Cache::forget(LegalDocumentService::CACHE_KEY));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'is_material' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(LegalAcceptance::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->whereNull('published_at');
    }

    public function title(): string
    {
        return self::TITLES[$this->type];
    }

    public function publicUrl(): string
    {
        return route(self::PUBLIC_ROUTES[$this->type]);
    }
}
