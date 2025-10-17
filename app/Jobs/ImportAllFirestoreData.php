<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
// use Kreait\Firebase\Contract\Firestore;
use App\Models\User;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceBooking;
use App\Models\Banner;
use App\Models\ServiceProvider;
use App\Services\FirestoreRestClient;

class ImportAllFirestoreData implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 600; // 10 minutes
    
    protected FirestoreRestClient $firestore;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->firestore = new FirestoreRestClient();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!config('firebase.sync_enabled', true)) {
            Log::info('Firebase sync is disabled');
            echo "Firebase sync is disabled\n";
            return;
        }

        Log::info('Starting Firestore import...');
        echo "Starting Firestore import...\n";

        try {
            echo "Importing users...\n";
            $this->importUsers();
            
            if (env('FIREBASE_IMPORT_ONLY_USERS', false)) {
                echo "Skipping services/orders/ratings import due to FIREBASE_IMPORT_ONLY_USERS=true\n";
                Log::info('Skipped other collections import as per FIREBASE_IMPORT_ONLY_USERS');
                return;
            }
            
            echo "Importing service categories...\n";
            if (!env('FIREBASE_IMPORT_ONLY_USERS', false)) {
                $this->importServiceCategories();
            } else {
                echo "  Skipped service categories import (FIREBASE_IMPORT_ONLY_USERS enabled)\n";
            }

            echo "Importing services...\n";
            // Skip if only importing users during diagnostics
            if (!env('FIREBASE_IMPORT_ONLY_USERS', false)) {
                $this->importServices();
            } else {
                echo "  Skipped services import (FIREBASE_IMPORT_ONLY_USERS enabled)\n";
            }
            
            echo "Importing service providers...\n";
            if (!env('FIREBASE_IMPORT_ONLY_USERS', false)) {
                $this->importServiceProviders();
            } else {
                echo "  Skipped service providers import (FIREBASE_IMPORT_ONLY_USERS enabled)\n";
            }
            
            echo "Importing orders (bookings)...\n";
            if (!env('FIREBASE_IMPORT_ONLY_USERS', false)) {
                $this->importOrders();
            } else {
                echo "  Skipped orders import (FIREBASE_IMPORT_ONLY_USERS enabled)\n";
            }
            
            echo "Importing banners...\n";
            if (!env('FIREBASE_IMPORT_ONLY_USERS', false)) {
                $this->importBanners();
            } else {
                echo "  Skipped banners import (FIREBASE_IMPORT_ONLY_USERS enabled)\n";
            }

            echo "Importing ratings...\n";
            if (!env('FIREBASE_IMPORT_ONLY_USERS', false)) {
                $this->importRatings();
                $this->importReviews();
            } else {
                echo "  Skipped ratings import (FIREBASE_IMPORT_ONLY_USERS enabled)\n";
            }

            Log::info('Firestore import completed successfully');
            echo "Firestore import completed successfully\n";
        } catch (\Exception $e) {
            Log::error('Firestore import failed: ' . $e->getMessage());
            echo "ERROR: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            throw $e;
        }
    }

    protected function importServiceCategories()
    {
        try {
            echo "Fetching serviceCategories from Firestore (REST)...\n";
            $client = new FirestoreRestClient();
            $pageToken = null; $count = 0; $processed = 0;

            do {
                [$documents, $nextPageToken] = $client->listDocuments('serviceCategories', 100, $pageToken);
                $batch = 0;
                foreach ($documents as $doc) {
                    $processed++;
                    $docId = basename($doc['name'] ?? '') ?: null;
                    if (!$docId) continue;
                    $data = FirestoreRestClient::decodeDocument($doc);

                    ServiceCategory::updateOrCreate(
                        ['firebase_id' => $docId],
                        [
                            'name' => $data['name'] ?? 'Unnamed',
                            'slug' => $data['slug'] ?? Str::slug($data['name'] ?? 'unnamed'),
                            'description' => $data['description'] ?? null,
                            'icon_url' => $data['iconUrl'] ?? $data['iconURL'] ?? null,
                            'color_code' => $data['colorCode'] ?? '#000000',
                            'sort_order' => $data['sortOrder'] ?? 0,
                            'is_active' => $data['isActive'] ?? true,
                            'last_synced_at' => now(),
                        ]
                    );
                    $count++; $batch++;
                }
                echo "  Processed serviceCategories batch: {$batch} (total {$count})\n";
                $pageToken = $nextPageToken;
            } while ($pageToken);

            Log::info("Imported {$count} service categories from Firestore (processed {$processed})");
            echo "✓ Imported {$count} service categories from Firestore (processed {$processed})\n";
        } catch (\Exception $e) {
            echo "ERROR in importServiceCategories: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    protected function importUsers()
    {
        try {
            echo "Fetching users from Firestore (REST)...\n";

            $client = new FirestoreRestClient();
            $pageToken = null;
            $count = 0;
            $processed = 0;

            do {
                [$documents, $nextPageToken] = $client->listDocuments('users', 50, $pageToken);
                $batchCount = 0;

                foreach ($documents as $doc) {
                    $processed++;
                    $namePath = $doc['name'] ?? '';
                    $docId = $namePath ? basename($namePath) : null;
                    if (!$docId) continue;

                    $data = FirestoreRestClient::decodeDocument($doc);

                    $firstName = $data['firstName'] ?? $data['first_name'] ?? '';
                    $lastName = $data['lastName'] ?? $data['last_name'] ?? '';
                    $fullName = trim($firstName . ' ' . $lastName);
                    $status = (isset($data['isAvailable']) && $data['isAvailable'] === false) ? 'inactive' : 'active';

                    User::updateOrCreate(
                        ['firebase_uid' => $docId],
                        [
                            'email' => $data['email'] ?? null,
                            'name' => $fullName ?: ($firstName ?: 'Unknown User'),
                            'phone' => $data['phone'] ?? null,
                            'avatar' => $data['personalPhoto'] ?? null,
                            'status' => $status,
                            'metadata' => json_encode([
                                'userType' => $data['userType'] ?? 'customer',
                                'language' => $data['language'] ?? 'en',
                                'job' => $data['job'] ?? null,
                                'idNumber' => $data['idNumber'] ?? null,
                                'idPhoto' => $data['idPhoto'] ?? null,
                                'personalPhoto' => $data['personalPhoto'] ?? null,
                                'addresses' => $data['addresses'] ?? [],
                                'fcmToken' => $data['fcmToken'] ?? null,
                                'fcmPlatform' => $data['fcmPlatform'] ?? null,
                                'averageRating' => $data['averageRating'] ?? null,
                                'totalRatings' => $data['totalRatings'] ?? null,
                                'currentOrder' => $data['currentOrder'] ?? null,
                                'isAvailable' => $data['isAvailable'] ?? true,
                            ]),
                            'last_synced_at' => now(),
                            'created_at' => isset($data['createdAt'])
                                ? \Carbon\Carbon::parse($data['createdAt'])
                                : (isset($data['created_at']) ? \Carbon\Carbon::parse($data['created_at']) : now()),
                        ]
                    );

                    $count++;
                    $batchCount++;
                }

                echo "  Processed batch: {$batchCount} users (total {$count})\n";
                $pageToken = $nextPageToken;
            } while ($pageToken);

            Log::info("Imported {$count} users from Firestore (processed {$processed} documents)");
            echo "✓ Imported {$count} users from Firestore (processed {$processed} documents)\n";
            
        } catch (\Exception $e) {
            echo "ERROR in importUsers: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
            throw $e;
        }
    }
    protected function importServices()
    {
        try {
            echo "Fetching services from Firestore (REST)...\n";
            $client = new FirestoreRestClient();
            $pageToken = null;
            $count = 0;
            $processed = 0;

            do {
                [$documents, $nextPageToken] = $client->listDocuments('services', 50, $pageToken);
                $batch = 0;
                foreach ($documents as $doc) {
                    $processed++;
                    $docId = basename($doc['name'] ?? '') ?: null;
                    if (!$docId) continue;
                    $data = FirestoreRestClient::decodeDocument($doc);

                    // Find or create category based on service data
                    $category = null;
                    if (isset($data['category']) || isset($data['categoryId']) || isset($data['categoryName'])) {
                        $catName = $data['category'] ?? $data['categoryName'] ?? null;
                        if ($catName) {
                            $category = ServiceCategory::firstOrCreate(
                                ['name' => $catName],
                                ['slug' => Str::slug($catName), 'is_active' => true]
                            );
                        }
                    }

                    Service::updateOrCreate(
                        ['firebase_id' => $docId],
                        [
                            'name' => $data['name'] ?? $data['title'] ?? 'Unnamed Service',
                            'slug' => $data['slug'] ?? Str::slug($data['name'] ?? $data['title'] ?? 'unnamed'),
                            'description' => $data['description'] ?? null,
                            'short_description' => $data['shortDescription'] ?? $data['subtitle'] ?? null,
                            'category_id' => $category?->id,
                            'icon_url' => $data['iconUrl'] ?? $data['icon'] ?? $data['image'] ?? null,
                            'images' => $data['images'] ?? null,
                            'price_min' => $data['priceMin'] ?? $data['price'] ?? 0,
                            'price_max' => $data['priceMax'] ?? $data['price'] ?? 0,
                            'price_unit' => $data['priceUnit'] ?? 'per service',
                            'duration' => $data['duration'] ?? 60,
                            'is_active' => $data['isActive'] ?? $data['active'] ?? true,
                            'is_featured' => $data['isFeatured'] ?? $data['featured'] ?? false,
                            'sort_order' => $data['sortOrder'] ?? $data['order'] ?? 0,
                            'metadata' => $data['metadata'] ?? null,
                            'last_synced_at' => now(),
                        ]
                    );
                    $count++;
                    $batch++;
                }
                echo "  Processed services batch: {$batch} (total {$count})\n";
                $pageToken = $nextPageToken;
            } while ($pageToken);

            Log::info("Imported {$count} services from Firestore (processed {$processed})");
            echo "✓ Imported {$count} services from Firestore (processed {$processed})\n";
        } catch (\Exception $e) {
            echo "ERROR in importServices: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    protected function importOrders()
    {
        try {
            echo "Fetching orders from Firestore (REST)...\n";
            $client = new FirestoreRestClient();
            $pageToken = null;
            $count = 0;
            $processed = 0;

            do {
                [$documents, $nextPageToken] = $client->listDocuments('orders', 50, $pageToken);
                $batch = 0;
                foreach ($documents as $doc) {
                    $processed++;
                    $docId = basename($doc['name'] ?? '') ?: null;
                    if (!$docId) continue;
                    $data = FirestoreRestClient::decodeDocument($doc);

                    $user = isset($data['userId']) ? User::where('firebase_uid', $data['userId'])->first() : null;
                    $service = isset($data['serviceId']) ? Service::where('firebase_id', $data['serviceId'])->first() : null;
                    // Determine provider (technician) if present
                    $providerId = null;
                    $technicianFirebaseUid = $data['technicianId'] ?? $data['providerId'] ?? null;
                    if ($technicianFirebaseUid) {
                        $techUser = User::where('firebase_uid', $technicianFirebaseUid)->first();
                        if ($techUser) {
                            $provider = ServiceProvider::where('user_id', $techUser->id)
                                ->when($service?->id, fn($q) => $q->where('service_id', $service->id))
                                ->first();
                            if (!$provider) {
                                // Fallback: create a minimal provider record without service binding
                                $provider = ServiceProvider::firstOrCreate(
                                    ['user_id' => $techUser->id, 'service_id' => $service?->id],
                                    [
                                        'firebase_id' => null,
                                        'verification_status' => 'pending',
                                        'is_available' => true,
                                    ]
                                );
                            }
                            $providerId = $provider?->id;
                        }
                    }

                    // Parse address from Firestore structure
                    $address = null; $lat = null; $lng = null;
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
                        ['firebase_id' => $docId],
                        [
                            'booking_number' => $docId,
                            'customer_id' => $user?->id,
                            'service_id' => $service?->id,
                            'provider_id' => $providerId,
                            'status' => $data['status'] ?? 'pending',
                            'scheduled_at' => isset($data['scheduledDateTime'])
                                ? \Carbon\Carbon::parse($data['scheduledDateTime'])
                                : (isset($data['createdAt']) ? \Carbon\Carbon::parse($data['createdAt']) : null),
                            'completed_at' => isset($data['completedAt'])
                                ? \Carbon\Carbon::parse($data['completedAt'])
                                : null,
                            'customer_address' => $address,
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'price' => $data['subTotal'] ?? 0,
                            'total_amount' => $data['total'] ?? 0,
                            'payment_method' => $data['paymentMethod'] ?? 'cash',
                            'payment_status' => ($data['status'] ?? '') === 'completed' ? 'paid' : 'pending',
                            'customer_notes' => isset($data['items']) ? json_encode($data['items']) : null,
                            'last_synced_at' => now(),
                        ]
                    );
                    $count++;
                    $batch++;
                }
                echo "  Processed orders batch: {$batch} (total {$count})\n";
                $pageToken = $nextPageToken;
            } while ($pageToken);

            Log::info("Imported {$count} orders from Firestore (processed {$processed})");
            echo "✓ Imported {$count} orders from Firestore (processed {$processed})\n";
        } catch (\Exception $e) {
            echo "ERROR in importOrders: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    protected function importServiceProviders()
    {
        try {
            echo "Fetching serviceProviders from Firestore (REST)...\n";
            $client = new FirestoreRestClient();
            $pageToken = null;
            $count = 0; $processed = 0;

            do {
                [$documents, $nextPageToken] = $client->listDocuments('serviceProviders', 50, $pageToken);
                $batch = 0;
                foreach ($documents as $doc) {
                    $processed++;
                    $docId = basename($doc['name'] ?? '') ?: null;
                    if (!$docId) continue;
                    $data = FirestoreRestClient::decodeDocument($doc);

                    $user = isset($data['userId']) ? User::where('firebase_uid', $data['userId'])->first() : null;
                    $service = isset($data['serviceId']) ? Service::where('firebase_id', $data['serviceId'])->first() : null;

                    ServiceProvider::updateOrCreate(
                        ['firebase_id' => $docId],
                        [
                            'user_id' => $user?->id,
                            'service_id' => $service?->id,
                            'custom_price' => $data['customPrice'] ?? null,
                            'bio' => $data['bio'] ?? null,
                            'experience_years' => $data['experienceYears'] ?? 0,
                            'rating' => $data['rating'] ?? 0,
                            'total_reviews' => $data['totalReviews'] ?? 0,
                            'completed_jobs' => $data['completedJobs'] ?? 0,
                            'verification_status' => $data['verificationStatus'] ?? 'pending',
                            'is_available' => $data['isAvailable'] ?? true,
                            'availability_schedule' => $data['availabilitySchedule'] ?? null,
                            'last_synced_at' => now(),
                        ]
                    );
                    $count++; $batch++;
                }
                echo "  Processed service providers batch: {$batch} (total {$count})\n";
                $pageToken = $nextPageToken;
            } while ($pageToken);

            Log::info("Imported {$count} service providers from Firestore (processed {$processed})");
            echo "✓ Imported {$count} service providers from Firestore (processed {$processed})\n";
        } catch (\Exception $e) {
            echo "ERROR in importServiceProviders: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    protected function importReviews()
    {
        try {
            echo "Fetching reviews from Firestore (REST)...\n";
            $client = new FirestoreRestClient();
            $pageToken = null; $count = 0; $processed = 0;

            do {
                [$documents, $nextPageToken] = $client->listDocuments('reviews', 100, $pageToken);
                $batch = 0;
                foreach ($documents as $doc) {
                    $processed++;
                    $data = FirestoreRestClient::decodeDocument($doc);
                    $bookingId = $data['bookingId'] ?? $data['orderId'] ?? null;
                    if (!$bookingId) continue;
                    $booking = ServiceBooking::where('firebase_id', $bookingId)->first();
                    if ($booking) {
                        $booking->update([
                            'rating' => $data['rating'] ?? $data['score'] ?? null,
                            'review' => $data['review'] ?? $data['comment'] ?? null,
                        ]);
                        $count++; $batch++;
                    }
                }
                echo "  Processed reviews batch: {$batch} (total {$count})\n";
                $pageToken = $nextPageToken;
            } while ($pageToken);

            Log::info("Imported {$count} reviews from Firestore (processed {$processed})");
            echo "✓ Imported {$count} reviews from Firestore (processed {$processed})\n";
        } catch (\Exception $e) {
            echo "ERROR in importReviews: " . $e->getMessage() . "\n";
            echo "  (Continuing despite reviews error)\n";
        }
    }

    protected function importRatings()
    {
        try {
            echo "Fetching ratings from Firestore (REST)...\n";
            $client = new FirestoreRestClient();
            $pageToken = null;
            $count = 0;
            $processed = 0;

            do {
                [$documents, $nextPageToken] = $client->listDocuments('ratings', 100, $pageToken);
                $batch = 0;
                foreach ($documents as $doc) {
                    $processed++;
                    $data = FirestoreRestClient::decodeDocument($doc);
                    $orderId = $data['orderId'] ?? $data['bookingId'] ?? null;
                    if (!$orderId) continue;
                    $booking = ServiceBooking::where('firebase_id', $orderId)->first();
                    if ($booking) {
                        $booking->update([
                            'rating' => $data['rating'] ?? $data['score'] ?? null,
                            'review' => $data['review'] ?? $data['comment'] ?? null,
                        ]);
                        $count++;
                        $batch++;
                    }
                }
                echo "  Processed ratings batch: {$batch} (total {$count})\n";
                $pageToken = $nextPageToken;
            } while ($pageToken);

            Log::info("Imported {$count} ratings from Firestore (processed {$processed})");
            echo "✓ Imported {$count} ratings from Firestore (processed {$processed})\n";
        } catch (\Exception $e) {
            echo "ERROR in importRatings: " . $e->getMessage() . "\n";
            // Don't throw - ratings are optional
            echo "  (Continuing despite ratings error)\n";
        }
    }

    protected function importBanners()
    {
        try {
            echo "Fetching banners from Firestore (REST)...\n";
            $client = new FirestoreRestClient();
            $pageToken = null; $count = 0; $processed = 0;

            do {
                [$documents, $nextPageToken] = $client->listDocuments('banners', 100, $pageToken);
                $batch = 0;
                foreach ($documents as $doc) {
                    $processed++;
                    $docId = basename($doc['name'] ?? '') ?: null;
                    if (!$docId) continue;
                    $data = FirestoreRestClient::decodeDocument($doc);

                    Banner::updateOrCreate(
                        ['firebase_id' => $docId],
                        [
                            'title' => $data['title'] ?? 'Unnamed Banner',
                            'description' => $data['description'] ?? null,
                            'image_url' => $data['imageUrl'] ?? $data['imageURL'] ?? null,
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
                    $count++; $batch++;
                }
                echo "  Processed banners batch: {$batch} (total {$count})\n";
                $pageToken = $nextPageToken;
            } while ($pageToken);

            Log::info("Imported {$count} banners from Firestore (processed {$processed})");
            echo "✓ Imported {$count} banners from Firestore (processed {$processed})\n";
        } catch (\Exception $e) {
            echo "ERROR in importBanners: " . $e->getMessage() . "\n";
            echo "  (Continuing despite banners error)\n";
        }
    }
}
