# Laravel + Filament Admin Panel Instructions for ProServe Flutter App

## Project Overview
This is a Laravel + Filament admin panel for managing the ProServe Flutter application built with **Firebase Firestore** as the primary database.

## CRITICAL ARCHITECTURE REQUIREMENTS

### Database Strategy & Data Flow

#### Primary Data Source: **Firebase Firestore**
- **Firestore is the source of truth** for all mobile app data
- Admin panel **fetches and caches** Firestore data into PostgreSQL
- Admin panel has **full CRUD capabilities** on Firestore
- All changes in admin panel **immediately update Firestore**

#### Admin Panel Database: **PostgreSQL**
- Acts as a **local cache and admin-specific storage**
- Stores admin users, roles, permissions (NOT synced to Firebase)
- Caches Firestore data for fast queries and reporting
- Stores sync logs, analytics, and admin activity logs

#### Data Synchronization Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    DATA FLOW ARCHITECTURE                        │
└─────────────────────────────────────────────────────────────────┘

FIRESTORE (Source of Truth)
    ↓ ↑
    ↓ ↑ [Background Jobs - Continuous Sync]
    ↓ ↑
POSTGRESQL (Admin Cache)
    ↓
    ↓ [Filament Resources]
    ↓
ADMIN PANEL (View & Edit)

MOBILE APP → Reads/Writes directly to → FIRESTORE
ADMIN PANEL → Reads from PostgreSQL Cache → Writes to FIRESTORE → Updates PostgreSQL
```

### User Management Strategy

#### Admin Users (PostgreSQL Only)
- Created **exclusively in admin panel**
- Stored **only in PostgreSQL**
- Have roles: super_admin, admin, moderator
- Access admin panel with Laravel authentication
- **NOT synced to Firebase**

#### App Users (Firestore Primary)
- Created via **mobile app** (Firebase Authentication)
- **Fetched from Firestore** into PostgreSQL cache
- Admin can view, edit, suspend, delete
- Changes in admin panel **sync back to Firestore**
- Roles: service_provider, customer

### Background Job Requirements

#### Job 1: Initial Firestore Import
```php
// app/Jobs/ImportFirestoreData.php
// Runs once or on-demand to import all Firestore collections
- Import users from Firebase Auth + Firestore
- Import services, categories, bookings
- Import app configuration, banners, navigation
- Create PostgreSQL records with firebase_id reference
```

#### Job 2: Continuous Firestore Sync
```php
// app/Jobs/SyncFirestoreChanges.php
// Runs every 5 minutes (configurable)
- Listen to Firestore changes using timestamps
- Update PostgreSQL cache with new/modified documents
- Mark deleted documents
- Log sync status
```

#### Job 3: Real-time Change Listener
```php
// app/Jobs/FirestoreChangeListener.php
// Uses Firebase Realtime Listeners (optional)
- Subscribe to Firestore collection changes
- Immediately update PostgreSQL when Firestore changes
- Push notifications to admin panel users
```

#### Job 4: Admin Changes to Firestore
```php
// app/Jobs/SyncAdminChangesToFirestore.php
// Triggered on every admin panel CRUD operation
- Immediately update Firestore when admin makes changes
- Update PostgreSQL cache after successful Firestore update
- Retry on failure with exponential backoff
```

## Implementation Details

### 1. Firebase Service Account Setup

**IMPORTANT**: You need to download the Firebase Admin SDK credentials:

1. Go to [Firebase Console](https://console.firebase.google.com/project/proserve-95f34/settings/serviceaccounts/adminsdk)
2. Click "Generate new private key"
3. Save as `storage/firebase-credentials.json`
4. Add to `.gitignore`

### 2. Required Packages

```bash
composer require kreait/laravel-firebase
composer require kreait/firebase-php
composer require google/cloud-firestore
```

### 3. Firestore Collections Structure

Based on your Flutter app, expected collections:

```
/users/{userId}
  - email, displayName, phoneNumber, photoURL, role, isActive
  - createdAt, updatedAt

/services/{serviceId}
  - name, description, categoryId, iconUrl, isActive, priceRange
  - providerId, rating, reviewCount
  - createdAt, updatedAt

/serviceCategories/{categoryId}
  - name, iconUrl, colorCode, sortOrder, isActive
  - createdAt, updatedAt

/bookings/{bookingId}
  - userId, serviceId, providerId, status
  - scheduledAt, completedAt, cancelledAt
  - price, paymentStatus, notes
  - createdAt, updatedAt

/appConfig/{configKey}
  - theme, navigation, features, banners
  - lastUpdatedBy (admin ID)
  - updatedAt

/notifications/{userId}/messages/{messageId}
  - title, body, type, isRead
  - createdAt, readAt
```

### 4. PostgreSQL Schema (Cache Tables)

All tables include `firebase_id` to link with Firestore documents:

```sql
-- Admin-only tables (NOT synced)
CREATE TABLE admin_users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Cached Firestore data
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    firebase_uid VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255),
    display_name VARCHAR(255),
    phone_number VARCHAR(50),
    photo_url TEXT,
    role VARCHAR(50),
    is_active BOOLEAN DEFAULT true,
    last_synced_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE services (
    id BIGSERIAL PRIMARY KEY,
    firebase_id VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category_id BIGINT,
    provider_id BIGINT,
    icon_url TEXT,
    is_active BOOLEAN DEFAULT true,
    price_range VARCHAR(50),
    rating DECIMAL(3,2),
    review_count INTEGER DEFAULT 0,
    last_synced_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE service_categories (
    id BIGSERIAL PRIMARY KEY,
    firebase_id VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    icon_url TEXT,
    color_code VARCHAR(7),
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    last_synced_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE bookings (
    id BIGSERIAL PRIMARY KEY,
    firebase_id VARCHAR(255) UNIQUE NOT NULL,
    user_id BIGINT,
    service_id BIGINT,
    provider_id BIGINT,
    status VARCHAR(50),
    scheduled_at TIMESTAMP,
    completed_at TIMESTAMP,
    cancelled_at TIMESTAMP,
    price DECIMAL(10,2),
    payment_status VARCHAR(50),
    notes TEXT,
    last_synced_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Sync management
CREATE TABLE firestore_sync_logs (
    id BIGSERIAL PRIMARY KEY,
    collection VARCHAR(100),
    document_id VARCHAR(255),
    action VARCHAR(50), -- fetch, create, update, delete
    direction VARCHAR(50), -- firestore_to_postgres, postgres_to_firestore
    status VARCHAR(50), -- success, failed, pending
    error_message TEXT,
    attempted_at TIMESTAMP,
    completed_at TIMESTAMP
);

CREATE TABLE firestore_sync_status (
    id BIGSERIAL PRIMARY KEY,
    collection VARCHAR(100) UNIQUE NOT NULL,
    last_sync_at TIMESTAMP,
    last_document_timestamp TIMESTAMP,
    total_documents INTEGER DEFAULT 0,
    status VARCHAR(50)
);
```

### 5. Background Job Implementation

#### Import All Firestore Data (One-time/On-demand)

```php
// app/Jobs/ImportAllFirestoreData.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Contract\Firestore;
use App\Models\User;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class ImportAllFirestoreData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    
    protected Firestore $firestore;

    public function __construct()
    {
        $this->firestore = app(Firestore::class);
    }

    public function handle()
    {
        Log::info('Starting Firestore import...');

        $this->importUsers();
        $this->importServiceCategories();
        $this->importServices();
        $this->importBookings();
        $this->importAppConfig();

        Log::info('Firestore import completed successfully');
    }

    protected function importUsers()
    {
        $collection = $this->firestore->database()->collection('users');
        $documents = $collection->documents();

        foreach ($documents as $document) {
            if ($document->exists()) {
                $data = $document->data();
                
                User::updateOrCreate(
                    ['firebase_uid' => $document->id()],
                    [
                        'email' => $data['email'] ?? null,
                        'display_name' => $data['displayName'] ?? null,
                        'phone_number' => $data['phoneNumber'] ?? null,
                        'photo_url' => $data['photoURL'] ?? null,
                        'role' => $data['role'] ?? 'customer',
                        'is_active' => $data['isActive'] ?? true,
                        'last_synced_at' => now(),
                        'created_at' => isset($data['createdAt']) 
                            ? \Carbon\Carbon::parse($data['createdAt']) 
                            : now(),
                    ]
                );
            }
        }

        Log::info('Imported users from Firestore');
    }

    protected function importServiceCategories()
    {
        $collection = $this->firestore->database()->collection('serviceCategories');
        $documents = $collection->documents();

        foreach ($documents as $document) {
            if ($document->exists()) {
                $data = $document->data();
                
                ServiceCategory::updateOrCreate(
                    ['firebase_id' => $document->id()],
                    [
                        'name' => $data['name'] ?? 'Unnamed',
                        'icon_url' => $data['iconUrl'] ?? null,
                        'color_code' => $data['colorCode'] ?? '#000000',
                        'sort_order' => $data['sortOrder'] ?? 0,
                        'is_active' => $data['isActive'] ?? true,
                        'last_synced_at' => now(),
                    ]
                );
            }
        }

        Log::info('Imported service categories from Firestore');
    }

    protected function importServices()
    {
        $collection = $this->firestore->database()->collection('services');
        $documents = $collection->documents();

        foreach ($documents as $document) {
            if ($document->exists()) {
                $data = $document->data();
                
                $category = ServiceCategory::where('firebase_id', $data['categoryId'] ?? null)->first();
                
                Service::updateOrCreate(
                    ['firebase_id' => $document->id()],
                    [
                        'name' => $data['name'] ?? 'Unnamed Service',
                        'description' => $data['description'] ?? null,
                        'category_id' => $category?->id,
                        'icon_url' => $data['iconUrl'] ?? null,
                        'is_active' => $data['isActive'] ?? true,
                        'price_range' => $data['priceRange'] ?? null,
                        'rating' => $data['rating'] ?? 0,
                        'review_count' => $data['reviewCount'] ?? 0,
                        'last_synced_at' => now(),
                    ]
                );
            }
        }

        Log::info('Imported services from Firestore');
    }

    protected function importBookings()
    {
        $collection = $this->firestore->database()->collection('bookings');
        $documents = $collection->documents();

        foreach ($documents as $document) {
            if ($document->exists()) {
                $data = $document->data();
                
                $user = User::where('firebase_uid', $data['userId'] ?? null)->first();
                $service = Service::where('firebase_id', $data['serviceId'] ?? null)->first();
                
                Booking::updateOrCreate(
                    ['firebase_id' => $document->id()],
                    [
                        'user_id' => $user?->id,
                        'service_id' => $service?->id,
                        'status' => $data['status'] ?? 'pending',
                        'scheduled_at' => isset($data['scheduledAt']) 
                            ? \Carbon\Carbon::parse($data['scheduledAt']) 
                            : null,
                        'price' => $data['price'] ?? 0,
                        'payment_status' => $data['paymentStatus'] ?? 'pending',
                        'notes' => $data['notes'] ?? null,
                        'last_synced_at' => now(),
                    ]
                );
            }
        }

        Log::info('Imported bookings from Firestore');
    }

    protected function importAppConfig()
    {
        // Import app configuration, banners, navigation, etc.
        Log::info('Imported app config from Firestore');
    }
}
```

#### Continuous Sync Job (Scheduled)

```php
// app/Jobs/SyncFirestoreChanges.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Contract\Firestore;
use App\Models\FirestoreSyncStatus;
use Illuminate\Support\Facades\Log;

class SyncFirestoreChanges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Firestore $firestore;
    protected string $collection;

    public function __construct(string $collection)
    {
        $this->firestore = app(Firestore::class);
        $this->collection = $collection;
    }

    public function handle()
    {
        $syncStatus = FirestoreSyncStatus::firstOrCreate(
            ['collection' => $this->collection],
            ['last_sync_at' => now()->subYear()]
        );

        $query = $this->firestore->database()
            ->collection($this->collection)
            ->where('updatedAt', '>', $syncStatus->last_document_timestamp ?? now()->subYear());

        $documents = $query->documents();
        $count = 0;

        foreach ($documents as $document) {
            if ($document->exists()) {
                $this->syncDocument($this->collection, $document->id(), $document->data());
                $count++;
            }
        }

        $syncStatus->update([
            'last_sync_at' => now(),
            'last_document_timestamp' => now(),
            'total_documents' => $syncStatus->total_documents + $count,
            'status' => 'completed',
        ]);

        Log::info("Synced {$count} documents from {$this->collection}");
    }

    protected function syncDocument(string $collection, string $documentId, array $data)
    {
        // Implement collection-specific sync logic
        match($collection) {
            'users' => $this->syncUser($documentId, $data),
            'services' => $this->syncService($documentId, $data),
            'bookings' => $this->syncBooking($documentId, $data),
            default => null,
        };
    }

    protected function syncUser(string $firebaseUid, array $data)
    {
        User::updateOrCreate(
            ['firebase_uid' => $firebaseUid],
            [
                'email' => $data['email'] ?? null,
                'display_name' => $data['displayName'] ?? null,
                'phone_number' => $data['phoneNumber'] ?? null,
                'photo_url' => $data['photoURL'] ?? null,
                'role' => $data['role'] ?? 'customer',
                'is_active' => $data['isActive'] ?? true,
                'last_synced_at' => now(),
            ]
        );
    }

    // Add similar methods for other collections...
}
```

#### Sync Admin Changes to Firestore

```php
// app/Traits/SyncToFirestore.php
namespace App\Traits;

use Kreait\Firebase\Contract\Firestore;
use App\Models\FirestoreSyncLog;
use Illuminate\Support\Facades\Log;

trait SyncToFirestore
{
    protected static function bootSyncToFirestore()
    {
        static::created(function ($model) {
            dispatch(function () use ($model) {
                $model->pushToFirestore('create');
            })->afterResponse();
        });

        static::updated(function ($model) {
            dispatch(function () use ($model) {
                $model->pushToFirestore('update');
            })->afterResponse();
        });

        static::deleted(function ($model) {
            dispatch(function () use ($model) {
                $model->pushToFirestore('delete');
            })->afterResponse();
        });
    }

    abstract public function getFirestoreCollection(): string;
    abstract public function getFirestoreDocumentId(): string;
    abstract public function toFirestoreArray(): array;

    public function pushToFirestore(string $action)
    {
        $firestore = app(Firestore::class);
        $collection = $this->getFirestoreCollection();
        $documentId = $this->getFirestoreDocumentId();

        try {
            if ($action === 'delete') {
                $firestore->database()
                    ->collection($collection)
                    ->document($documentId)
                    ->delete();
            } else {
                $data = array_merge($this->toFirestoreArray(), [
                    'updatedAt' => now()->toIso8601String(),
                    'updatedBy' => auth()->id() ?? 'admin',
                ]);

                $firestore->database()
                    ->collection($collection)
                    ->document($documentId)
                    ->set($data, ['merge' => true]);
            }

            $this->update(['last_synced_at' => now()]);

            FirestoreSyncLog::create([
                'collection' => $collection,
                'document_id' => $documentId,
                'action' => $action,
                'direction' => 'postgres_to_firestore',
                'status' => 'success',
                'attempted_at' => now(),
                'completed_at' => now(),
            ]);

            Log::info("Synced {$collection}/{$documentId} to Firestore: {$action}");

        } catch (\Exception $e) {
            FirestoreSyncLog::create([
                'collection' => $collection,
                'document_id' => $documentId,
                'action' => $action,
                'direction' => 'postgres_to_firestore',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'attempted_at' => now(),
            ]);

            Log::error("Failed to sync to Firestore: {$e->getMessage()}");
        }
    }
}
```

### 6. Scheduled Jobs Configuration

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Import all data once daily (full sync)
    $schedule->job(new ImportAllFirestoreData)
        ->dailyAt('02:00')
        ->onOneServer();

    // Continuous sync every 5 minutes
    $schedule->job(new SyncFirestoreChanges('users'))
        ->everyFiveMinutes()
        ->onOneServer();
    
    $schedule->job(new SyncFirestoreChanges('services'))
        ->everyFiveMinutes()
        ->onOneServer();
    
    $schedule->job(new SyncFirestoreChanges('bookings'))
        ->everyFiveMinutes()
        ->onOneServer();

    $schedule->job(new SyncFirestoreChanges('serviceCategories'))
        ->everyTenMinutes()
        ->onOneServer();
}
```

### 7. Filament Resources with Full CRUD

All Filament resources will:
- ✅ Display cached Firestore data from PostgreSQL
- ✅ Allow full CRUD operations
- ✅ Automatically sync changes to Firestore
- ✅ Show sync status and last sync time
- ✅ Provide manual "Force Sync" button
- ✅ Display warnings if sync fails

Example Filament Resource:

```php
// app/Filament/Resources/ServiceResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms\Components\Section;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Service Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(4),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required(),
                        Forms\Components\FileUpload::make('icon_url')
                            ->image()
                            ->directory('service-icons'),
                        Forms\Components\TextInput::make('price_range')
                            ->placeholder('e.g., $50-$100'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),
                
                Section::make('Firestore Sync Status')
                    ->schema([
                        Forms\Components\Placeholder::make('firebase_id')
                            ->content(fn ($record) => $record?->firebase_id ?? 'Not synced yet'),
                        Forms\Components\Placeholder::make('last_synced_at')
                            ->content(fn ($record) => $record?->last_synced_at?->diffForHumans() ?? 'Never'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon_url')
                    ->size(40)
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_range'),
                Tables\Columns\BadgeColumn::make('is_active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => 
                        $record->last_synced_at?->diffInMinutes() > 10 ? 'warning' : 'success'
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('forceSync')
                    ->icon('heroicon-o-refresh')
                    ->color('warning')
                    ->action(function (Service $record) {
                        $record->pushToFirestore('update');
                        \Filament\Notifications\Notification::make()
                            ->title('Sync initiated')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('syncToFirestore')
                    ->label('Sync Selected to Firestore')
                    ->icon('heroicon-o-refresh')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->pushToFirestore('update');
                        }
                        \Filament\Notifications\Notification::make()
                            ->title('Batch sync initiated')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
```

### 8. Admin Dashboard Widgets

```php
// app/Filament/Widgets/FirestoreSyncStatus.php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\FirestoreSyncLog;
use App\Models\FirestoreSyncStatus;

class FirestoreSyncStatus extends BaseWidget
{
    protected function getCards(): array
    {
        $recentSyncs = FirestoreSyncLog::where('created_at', '>=', now()->subHour())->count();
        $failedSyncs = FirestoreSyncLog::where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();
        
        $lastSync = FirestoreSyncStatus::max('last_sync_at');

        return [
            Card::make('Recent Syncs (1h)', $recentSyncs)
                ->description('Firestore operations')
                ->color('success'),
            
            Card::make('Failed Syncs (24h)', $failedSyncs)
                ->description('Requires attention')
                ->color($failedSyncs > 0 ? 'danger' : 'success'),
            
            Card::make('Last Sync', $lastSync?->diffForHumans() ?? 'Never')
                ->description('Most recent sync')
                ->color('primary'),
        ];
    }
}
```

## Summary of Admin Panel Capabilities

### ✅ What the Admin Panel CAN Do:

1. **Fetch All Data from Firestore**
   - Automatic background jobs import all collections
   - Scheduled continuous sync every 5 minutes
   - Manual full import on-demand

2. **Full CRUD on Firestore**
   - Create new services, categories, bookings
   - Update any Firestore document
   - Delete Firestore documents
   - All changes immediately reflect in Firestore

3. **Admin User Management**
   - Create admin users (PostgreSQL only)
   - Admins don't sync to Firestore
   - Role-based access control

4. **App User Management**
   - View all mobile app users
   - Edit user profiles (syncs to Firestore)
   - Suspend/activate users
   - Cannot create Firebase Auth users (users register via mobile app)

5. **Content Management**
   - Update app theme colors, fonts (live updates)
   - Manage banners, navigation
   - Configure feature flags
   - Update static pages

6. **Monitoring**
   - View sync logs
   - Track sync failures
   - Performance metrics
   - Analytics dashboard

### ⚠️ Important Notes:

- **Admin panel writes to Firestore immediately** on every change
- **PostgreSQL acts as a cache** for fast queries and reporting
- **Background jobs keep cache updated** from Firestore changes
- **All mobile app data comes from Firestore**, not PostgreSQL
- **Admin users are separate** and don't appear in the mobile app

## Deployment Steps

1. Download Firebase Admin SDK credentials
2. Configure environment variables
3. Run migrations
4. Run initial import: `php artisan queue:work --queue=default`
5. Dispatch initial import: `ImportAllFirestoreData::dispatch()`
6. Set up scheduled jobs: `php artisan schedule:work`
7. Configure queue workers for continuous operation
