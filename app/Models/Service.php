<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\SyncToFirestore;

class Service extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, SyncToFirestore;

    protected $fillable = [
        'firebase_id',
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'icon_url',
        'images',
        'price_min',
        'price_max',
        'price_unit',
        'duration',
        'is_active',
        'is_featured',
        'sort_order',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'images' => 'array',
        'metadata' => 'array',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'duration' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function providers()
    {
        return $this->hasMany(ServiceProvider::class);
    }

    public function bookings()
    {
        return $this->hasMany(ServiceBooking::class);
    }

    /**
     * Register media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('icon')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp']);
    }

    /**
     * Get Firestore collection name
     */
    public function getFirestoreCollection(): string
    {
        return 'services';
    }

    /**
     * Get Firestore document ID
     */
    public function getFirestoreDocumentId(): string
    {
        return $this->firebase_id ?? $this->id;
    }

    /**
     * Convert model to Firestore array
     */
    public function toFirestoreArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'shortDescription' => $this->short_description,
            'categoryId' => $this->category?->firebase_id,
            'iconUrl' => $this->getFirstMediaUrl('icon') ?: $this->icon_url,
            'images' => $this->getMedia('images')->map(fn($m) => $m->getUrl())->toArray(),
            'priceMin' => (float) $this->price_min,
            'priceMax' => (float) $this->price_max,
            'priceUnit' => $this->price_unit,
            'priceRange' => "{$this->price_min}-{$this->price_max}",
            'duration' => $this->duration,
            'isActive' => $this->is_active,
            'isFeatured' => $this->is_featured,
            'sortOrder' => $this->sort_order,
            'rating' => 0,
            'reviewCount' => 0,
            'metadata' => $this->metadata ?? [],
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
