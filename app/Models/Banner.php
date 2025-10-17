<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\SyncToFirestore;

class Banner extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, SyncToFirestore;

    protected $fillable = [
        'firebase_id',
        'title',
        'description',
        'image_url',
        'link_type',
        'link_value',
        'button_text',
        'sort_order',
        'is_active',
        'start_date',
        'end_date',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Register media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner_image')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Get Firestore collection name
     */
    public function getFirestoreCollection(): string
    {
        return 'banners';
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
            'title' => $this->title,
            'description' => $this->description,
            'imageUrl' => $this->getFirstMediaUrl('banner_image') ?: $this->image_url,
            'linkType' => $this->link_type,
            'linkValue' => $this->link_value,
            'buttonText' => $this->button_text,
            'sortOrder' => $this->sort_order,
            'isActive' => $this->is_active,
            'startDate' => $this->start_date?->toIso8601String(),
            'endDate' => $this->end_date?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
