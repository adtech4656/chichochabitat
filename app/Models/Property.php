<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_NEEDS_INFO = 'needs_info';

    protected $fillable = [
        'country_code',
        'tenant_id',
        'agent_id',
        'reference',
        'title',
        'slug',
        'description',
        'intent',
        'type',
        'sub_type',
        'district',
        'city',
        'price',
        'currency',
        'platform_fee',
        'cover_image',
        'gallery',
        'tags',
        'bedrooms',
        'bathrooms',
        'area',
        'latitude',
        'longitude',
        'status',
        'required_shots',
        'captured_shots',
        'documents_count',
        'gps_delta_m',
        'agent_kyc_status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'published_at',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'platform_fee' => 'integer',
            'gallery' => 'array',
            'tags' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'captured_shots' => 'integer',
            'required_shots' => 'integer',
            'documents_count' => 'integer',
            'gps_delta_m' => 'integer',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $property): void {
            $property->slug ??= Str::slug($property->title).'-'.Str::lower(Str::random(5));
            $property->reference ??= 'CCH-'.now()->format('Y').'-'.Str::upper(Str::random(6));
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documentAccesses(): HasMany
    {
        return $this->hasMany(PropertyDocumentAccess::class);
    }

    public function saves(): HasMany
    {
        return $this->hasMany(SavedProperty::class);
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_properties')->withTimestamps();
    }

    /** Only approved listings may ever reach the public site. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)->whereNotNull('published_at');
    }

    /** The approve action is blocked until every mandatory artefact exists. */
    public function isApprovable(): bool
    {
        return $this->captured_shots >= $this->required_shots
            && $this->documents_count >= 3
            && $this->agent_kyc_status === 'approved'
            && $this->status !== self::STATUS_APPROVED;
    }

    public function missingShots(): int
    {
        return max(0, $this->required_shots - $this->captured_shots);
    }

    public function missingDocuments(): int
    {
        return max(0, 3 - $this->documents_count);
    }

    /** What the customer pays: agent price plus the platform fee (§9). */
    public function customerPrice(): int
    {
        return $this->price + $this->platform_fee;
    }

    /**
     * Shape consumed by every React listing surface. Keeping it here means the
     * public site and the API can never drift apart.
     *
     * @return array<string, mixed>
     */
    public function toCardArray(?User $viewer = null): array
    {
        $viewer ??= auth()->user();
        $agentId = $this->agent_id;
        $dealsCount = $agentId
            ? Cache::remember("agent_deals_{$agentId}", 120, fn (): int => Property::query()->where('agent_id', $agentId)->where('status', self::STATUS_APPROVED)->count())
            : 0;

        $isSaved = false;
        if ($viewer instanceof User) {
            if ($this->relationLoaded('saves')) {
                $isSaved = $this->saves->contains('user_id', $viewer->id);
            } else {
                $isSaved = Cache::remember("user_{$viewer->id}_saved_{$this->id}", 60, fn (): bool => $this->saves()->where('user_id', $viewer->id)->exists());
            }
        }

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'location' => "{$this->district}, {$this->city}",
            'district' => $this->district,
            'city' => $this->city,
            'price' => $this->price,
            'currency' => $this->currency,
            'intent' => $this->intent,
            'intent_label' => __($this->intent === 'rent' ? 'prop.month' : 'prop.sale'),
            'type' => $this->type,
            'sub_type' => $this->sub_type,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'area' => $this->area,
            'image' => $this->cover_image,
            'gallery' => $this->gallery ?: array_filter([$this->cover_image]),
            'tags' => $this->tags ?: [],
            'description' => $this->description,
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'published' => $this->published_at?->toDateString() ?? $this->created_at?->toDateString(),
            'views' => $this->views,
            'href' => "/properties/{$this->slug}",
            'saved' => $isSaved,
            'agent' => [
                'id' => $this->agent?->id,
                'name' => $this->agent?->name ?? 'ChiChoc-Habitat',
                'agency' => 'ChiChoc-Habitat',
                'initials' => $this->agent?->initials() ?? 'CH',
                'rating' => 4.9,
                'deals' => $dealsCount,
                'verified' => $this->agent_kyc_status === 'approved',
                'since' => (string) $this->agent?->created_at?->year,
                'response' => 'Same day',
                'phone' => $this->agent?->phone,
            ],
            'verified' => [
                'agent' => $this->agent_kyc_status === 'approved',
                'location' => $this->gps_delta_m <= 100,
                'photos' => $this->captured_shots >= $this->required_shots,
                'video' => true,
                'documents' => $this->documents_count >= 3,
                'reviewer' => 'Technical review',
                'reviewed_at' => $this->reviewed_at?->toDateString(),
            ],
        ];
    }
}
