<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingEvent extends Model
{
    protected $fillable = [
        'service_booking_id',
        'event_type',
        'from_status',
        'to_status',
        'description',
        'metadata',
        'performed_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(ServiceBooking::class, 'service_booking_id');
    }

    public static function logStatusChange(ServiceBooking $booking, string $from, string $to, ?string $by = null): self
    {
        return static::create([
            'service_booking_id' => $booking->id,
            'event_type' => 'status_change',
            'from_status' => $from,
            'to_status' => $to,
            'description' => "Status changed from {$from} to {$to}",
            'performed_by' => $by ?? auth()->user()?->email ?? 'system',
        ]);
    }

    public static function logPayment(ServiceBooking $booking, string $description, array $meta = []): self
    {
        return static::create([
            'service_booking_id' => $booking->id,
            'event_type' => 'payment',
            'description' => $description,
            'metadata' => $meta,
            'performed_by' => auth()->user()?->email ?? 'system',
        ]);
    }

    public static function logNote(ServiceBooking $booking, string $note, ?string $by = null): self
    {
        return static::create([
            'service_booking_id' => $booking->id,
            'event_type' => 'note',
            'description' => $note,
            'performed_by' => $by ?? auth()->user()?->email ?? 'system',
        ]);
    }

    public function getIconAttribute(): string
    {
        return match ($this->event_type) {
            'status_change' => match ($this->to_status) {
                'pending' => 'heroicon-o-clock',
                'confirmed' => 'heroicon-o-check-circle',
                'assigned', 'accepted' => 'heroicon-o-user-plus',
                'in_progress', 'started' => 'heroicon-o-wrench-screwdriver',
                'completed' => 'heroicon-o-check-badge',
                'cancelled' => 'heroicon-o-x-circle',
                'refunded' => 'heroicon-o-arrow-uturn-left',
                default => 'heroicon-o-arrow-right',
            },
            'payment' => 'heroicon-o-banknotes',
            'note' => 'heroicon-o-chat-bubble-left-ellipsis',
            'assignment' => 'heroicon-o-user-plus',
            default => 'heroicon-o-information-circle',
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->event_type) {
            'status_change' => match ($this->to_status) {
                'completed' => 'emerald',
                'cancelled' => 'red',
                'refunded' => 'amber',
                'in_progress', 'started' => 'blue',
                'confirmed', 'assigned', 'accepted' => 'indigo',
                default => 'gray',
            },
            'payment' => 'emerald',
            'note' => 'sky',
            default => 'gray',
        };
    }
}
