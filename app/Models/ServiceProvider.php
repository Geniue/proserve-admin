<?php

namespace App\Models;

use App\Traits\SyncToFirestore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory, \App\Traits\SyncToFirestore;

    protected $fillable = [
        'firebase_id',
        'user_id',
        'service_id',
        'custom_price',
        'bio',
        'experience_years',
        'rating',
        'total_reviews',
        'completed_jobs',
        'verification_status',
        'is_available',
        'availability_schedule',
        'last_synced_at',
    ];

    protected $casts = [
        'availability_schedule' => 'array',
        'is_available' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Firestore sync mapping
    public function getFirestoreCollection(): string
    {
        return 'serviceProviders';
    }

    public function getFirestoreDocumentId(): string
    {
        return $this->firebase_id ?: (string) $this->id;
    }

    public function toFirestoreArray(): array
    {
        return [
            'userId' => optional($this->user)->firebase_uid,
            'serviceId' => optional($this->service)->firebase_id,
            'customPrice' => $this->custom_price,
            'bio' => $this->bio,
            'experienceYears' => $this->experience_years,
            'rating' => $this->rating,
            'totalReviews' => $this->total_reviews,
            'completedJobs' => $this->completed_jobs,
            'verificationStatus' => $this->verification_status,
            'isAvailable' => $this->is_available,
            'availabilitySchedule' => $this->availability_schedule,
        ];
    }
}

