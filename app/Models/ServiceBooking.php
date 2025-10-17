<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\SyncToFirestore;

class ServiceBooking extends Model
{
    use SoftDeletes, SyncToFirestore;

    protected $fillable = [
        'firebase_id',
        'booking_number',
        'customer_id',
        'provider_id',
        'service_id',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'customer_address',
        'latitude',
        'longitude',
        'price',
        'discount',
        'tax',
        'total_amount',
        'payment_method',
        'payment_status',
        'customer_notes',
        'provider_notes',
        'cancellation_reason',
        'rating',
        'review',
        'last_synced_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'rating' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get Firestore collection name
     */
    public function getFirestoreCollection(): string
    {
        return 'orders';
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
        // Parse address components
        $address = [];
        if ($this->customer_address) {
            $address['fullText'] = $this->customer_address;
        }
        if ($this->latitude) {
            $address['lat'] = (float) $this->latitude;
        }
        if ($this->longitude) {
            $address['lng'] = (float) $this->longitude;
        }

        return [
            'userId' => $this->customer?->firebase_uid,
            'serviceId' => $this->service?->firebase_id,
            'technicianId' => $this->provider_id,
            'status' => $this->status,
            'scheduledDateTime' => $this->scheduled_at?->toIso8601String(),
            'acceptedAt' => $this->started_at?->toIso8601String(),
            'assignedAt' => $this->started_at?->toIso8601String(),
            'completedAt' => $this->completed_at?->toIso8601String(),
            'address' => $address,
            'subTotal' => (float) $this->price,
            'discount' => (float) ($this->discount ?? 0),
            'total' => (float) $this->total_amount,
            'paymentMethod' => $this->payment_method ?? 'cash',
            'items' => $this->customer_notes ? json_decode($this->customer_notes, true) : [],
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
