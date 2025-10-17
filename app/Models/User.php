<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\SyncToFirestore;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, InteractsWithMedia, SyncToFirestore;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firebase_uid',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',
        'metadata',
        'last_synced_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'metadata' => 'array',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function serviceProviderProfiles()
    {
        return $this->hasMany(ServiceProvider::class);
    }

    public function bookings()
    {
        return $this->hasMany(ServiceBooking::class, 'customer_id');
    }

    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission($permissionSlug)
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionSlug) {
            $query->where('slug', $permissionSlug);
        })->exists();
    }

    /**
     * Register media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Get Firestore collection name
     */
    public function getFirestoreCollection(): string
    {
        return 'users';
    }

    /**
     * Get Firestore document ID
     */
    public function getFirestoreDocumentId(): string
    {
        return $this->firebase_uid ?? $this->id;
    }

    /**
     * Convert model to Firestore array
     * Only sync if user has firebase_uid (app users, not admin users)
     */
    public function toFirestoreArray(): array
    {
        // Don't sync admin-only users
        if (!$this->firebase_uid) {
            return [];
        }

        return [
            'email' => $this->email,
            'displayName' => $this->name,
            'phoneNumber' => $this->phone,
            'photoURL' => $this->getFirstMediaUrl('avatar') ?: $this->avatar,
            'role' => $this->roles->first()?->slug ?? 'customer',
            'isActive' => $this->status === 'active',
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
