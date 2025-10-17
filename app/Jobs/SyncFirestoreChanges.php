<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
// use Kreait\Firebase\Contract\Firestore;
use App\Services\FirestoreRestClient;
use App\Models\User;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceBooking;
use App\Models\Banner;
use App\Models\FirestoreSyncStatus;

class SyncFirestoreChanges implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 300; // 5 minutes
    
    protected string $collection;

    /**
     * Create a new job instance.
     */
    public function __construct(string $collection)
    {
        // Avoid resolving Firestore client at construction time
        $this->collection = $collection;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!config('firebase.sync_enabled', true)) {
            Log::info("Firebase sync is disabled for {$this->collection}");
            return;
        }

        Log::info("Starting sync for collection: {$this->collection}");

        try {
            $syncStatus = FirestoreSyncStatus::firstOrCreate(
                ['collection' => $this->collection],
                [
                    'last_sync_at' => now()->subYear(),
                    'last_document_timestamp' => now()->subYear(),
                    'total_documents' => 0,
                    'status' => 'pending',
                ]
            );

            // Query documents updated since last sync
            $lastTimestamp = $syncStatus->last_document_timestamp ?? now()->subYear();
            
            // Use REST client to fetch documents (no server-side filtering; do it in PHP)
            $client = new FirestoreRestClient();
            $pageToken = null;
            $count = 0;
            $latestTimestamp = $lastTimestamp;

            do {
                [$documents, $nextPageToken] = $client->listDocuments($this->collection, 200, $pageToken);
                foreach ($documents as $doc) {
                    $docId = basename($doc['name'] ?? '') ?: null;
                    if (!$docId) continue;
                    $data = FirestoreRestClient::decodeDocument($doc);

                    // Filter by updatedAt > lastTimestamp when present
                    $updatedAt = isset($data['updatedAt']) ? \Carbon\Carbon::parse($data['updatedAt']) : null;
                    if ($updatedAt && $updatedAt->lessThanOrEqualTo($lastTimestamp)) {
                        continue;
                    }

                    $this->syncDocument($this->collection, $docId, $data);
                    $count++;
                    if ($updatedAt && $updatedAt->greaterThan($latestTimestamp)) {
                        $latestTimestamp = $updatedAt;
                    }
                }
                $pageToken = $nextPageToken;
            } while ($pageToken);

            $syncStatus->update([
                'last_sync_at' => now(),
                'last_document_timestamp' => $latestTimestamp,
                'total_documents' => $syncStatus->total_documents + $count,
                'status' => 'completed',
            ]);

            Log::info("Synced {$count} documents from {$this->collection}");
        } catch (\Exception $e) {
            Log::error("Sync failed for {$this->collection}: {$e->getMessage()}");
            
            FirestoreSyncStatus::updateOrCreate(
                ['collection' => $this->collection],
                ['status' => 'failed']
            );
            
            throw $e;
        }
    }

    protected function syncDocument(string $collection, string $documentId, array $data)
    {
        match($collection) {
            'users' => $this->syncUser($documentId, $data),
            'services' => $this->syncService($documentId, $data),
            'serviceCategories' => $this->syncCategory($documentId, $data),
            'orders' => $this->syncOrder($documentId, $data),
            'bookings' => $this->syncOrder($documentId, $data), // Fallback for old name
            'banners' => $this->syncBanner($documentId, $data),
            default => Log::warning("Unknown collection: {$collection}"),
        };
    }

    protected function syncUser(string $firebaseUid, array $data)
    {
        User::updateOrCreate(
            ['firebase_uid' => $firebaseUid],
            [
                'email' => $data['email'] ?? null,
                'name' => $data['displayName'] ?? null,
                'phone' => $data['phoneNumber'] ?? null,
                'avatar' => $data['photoURL'] ?? null,
                'status' => ($data['isActive'] ?? true) ? 'active' : 'inactive',
                'last_synced_at' => now(),
            ]
        );
    }

    protected function syncCategory(string $firebaseId, array $data)
    {
        ServiceCategory::updateOrCreate(
            ['firebase_id' => $firebaseId],
            [
                'name' => $data['name'] ?? 'Unnamed',
                'slug' => $data['slug'] ?? Str::slug($data['name'] ?? 'unnamed'),
                'description' => $data['description'] ?? null,
                'icon_url' => $data['iconUrl'] ?? null,
                'color_code' => $data['colorCode'] ?? '#000000',
                'sort_order' => $data['sortOrder'] ?? 0,
                'is_active' => $data['isActive'] ?? true,
                'last_synced_at' => now(),
            ]
        );
    }

    protected function syncService(string $firebaseId, array $data)
    {
        $category = ServiceCategory::where('firebase_id', $data['categoryId'] ?? null)->first();
        
        Service::updateOrCreate(
            ['firebase_id' => $firebaseId],
            [
                'name' => $data['name'] ?? 'Unnamed Service',
                'slug' => $data['slug'] ?? Str::slug($data['name'] ?? 'unnamed'),
                'description' => $data['description'] ?? null,
                'short_description' => $data['shortDescription'] ?? null,
                'category_id' => $category?->id,
                'icon_url' => $data['iconUrl'] ?? null,
                'images' => isset($data['images']) ? json_encode($data['images']) : null,
                'price_min' => $data['priceMin'] ?? 0,
                'price_max' => $data['priceMax'] ?? 0,
                'price_unit' => $data['priceUnit'] ?? 'per service',
                'duration' => $data['duration'] ?? 60,
                'is_active' => $data['isActive'] ?? true,
                'is_featured' => $data['isFeatured'] ?? false,
                'sort_order' => $data['sortOrder'] ?? 0,
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
                'last_synced_at' => now(),
            ]
        );
    }

    protected function syncOrder(string $firebaseId, array $data)
    {
        $user = User::where('firebase_uid', $data['userId'] ?? null)->first();
        $service = Service::where('firebase_id', $data['serviceId'] ?? null)->first();
        
        // Parse address from Firestore structure
        $address = null;
        $lat = null;
        $lng = null;
        
        if (isset($data['address'])) {
            if (is_array($data['address'])) {
                $address = $data['address']['fullText'] ?? $data['address']['label'] ?? null;
                $lat = $data['address']['lat'] ?? null;
                $lng = $data['address']['lng'] ?? null;
            } else {
                $address = $data['address'];
            }
        }
        
        ServiceBooking::updateOrCreate(
            ['firebase_id' => $firebaseId],
            [
                'booking_number' => $firebaseId,
                'customer_id' => $user?->id,
                'service_id' => $service?->id,
                'status' => $data['status'] ?? 'pending',
                'scheduled_at' => isset($data['scheduledDateTime']) 
                    ? \Carbon\Carbon::parse($data['scheduledDateTime']) 
                    : (isset($data['createdAt']) ? \Carbon\Carbon::parse($data['createdAt']) : null),
                'started_at' => isset($data['acceptedAt']) 
                    ? \Carbon\Carbon::parse($data['acceptedAt']) 
                    : (isset($data['assignedAt']) ? \Carbon\Carbon::parse($data['assignedAt']) : null),
                'completed_at' => isset($data['completedAt']) 
                    ? \Carbon\Carbon::parse($data['completedAt']) 
                    : null,
                'customer_address' => $address,
                'latitude' => $lat,
                'longitude' => $lng,
                'price' => $data['subTotal'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'total_amount' => $data['total'] ?? 0,
                'payment_method' => $data['paymentMethod'] ?? 'cash',
                'payment_status' => $data['status'] == 'completed' ? 'paid' : 'pending',
                'customer_notes' => isset($data['items']) ? json_encode($data['items']) : null,
                'last_synced_at' => now(),
            ]
        );
    }

    // Keep old method name as alias for backward compatibility
    protected function syncBooking(string $firebaseId, array $data)
    {
        return $this->syncOrder($firebaseId, $data);
    }

    protected function syncBanner(string $firebaseId, array $data)
    {
        Banner::updateOrCreate(
            ['firebase_id' => $firebaseId],
            [
                'title' => $data['title'] ?? 'Unnamed Banner',
                'description' => $data['description'] ?? null,
                'image_url' => $data['imageUrl'] ?? null,
                'link_type' => $data['linkType'] ?? null,
                'link_value' => $data['linkValue'] ?? null,
                'button_text' => $data['buttonText'] ?? null,
                'sort_order' => $data['sortOrder'] ?? 0,
                'is_active' => $data['isActive'] ?? true,
                'start_date' => isset($data['startDate']) 
                    ? \Carbon\Carbon::parse($data['startDate']) 
                    : null,
                'end_date' => isset($data['endDate']) 
                    ? \Carbon\Carbon::parse($data['endDate']) 
                    : null,
                'last_synced_at' => now(),
            ]
        );
    }
}
