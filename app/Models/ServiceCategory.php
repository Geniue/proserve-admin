<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\SyncToFirestore;

class ServiceCategory extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, SyncToFirestore;

    protected $fillable = [
        'firebase_id',
        'name',
        'slug',
        'description',
        'icon_url',
        'color_code',
        'sort_order',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    /**
     * Register media collections for this model.
     */
    public function registerMediaCollections(): void
    {
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
        return 'serviceCategories';
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
            'slug' => $this->slug,
            'description' => $this->description,
            'iconUrl' => $this->getFirstMediaUrl('icon') ?: $this->icon_url,
            'colorCode' => $this->color_code,
            'sortOrder' => $this->sort_order,
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
