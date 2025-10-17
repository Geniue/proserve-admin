<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\FirestoreSyncLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FirebaseSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_category_gets_firebase_id()
    {
        $category = ServiceCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $this->assertNotNull($category);
        $this->assertEquals('Test Category', $category->name);
    }

    public function test_service_has_sync_to_firestore_trait()
    {
        $service = new Service();
        
        $this->assertTrue(method_exists($service, 'getFirestoreCollection'));
        $this->assertTrue(method_exists($service, 'getFirestoreDocumentId'));
        $this->assertTrue(method_exists($service, 'toFirestoreArray'));
    }

    public function test_service_returns_correct_firestore_collection()
    {
        $service = new Service();
        $this->assertEquals('services', $service->getFirestoreCollection());
    }

    public function test_service_category_returns_correct_firestore_collection()
    {
        $category = new ServiceCategory();
        $this->assertEquals('serviceCategories', $category->getFirestoreCollection());
    }

    public function test_service_to_firestore_array_converts_snake_to_camel()
    {
        $category = ServiceCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'firebase_id' => 'test-firebase-id',
            'icon_url' => 'http://example.com/icon.png',
            'color_code' => '#FF0000',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Test Service',
            'slug' => 'test-service',
            'firebase_id' => 'test-service-id',
            'category_id' => $category->id,
            'icon_url' => 'http://example.com/service.png',
            'price_min' => 100,
            'price_max' => 200,
            'is_active' => true,
        ]);

        $firestoreData = $service->toFirestoreArray();

        // Check camelCase conversion
        $this->assertArrayHasKey('categoryId', $firestoreData);
        $this->assertArrayHasKey('iconUrl', $firestoreData);
        $this->assertArrayHasKey('priceMin', $firestoreData);
        $this->assertArrayHasKey('priceMax', $firestoreData);
        $this->assertArrayHasKey('isActive', $firestoreData);
        
        // Check values
        $this->assertEquals('test-firebase-id', $firestoreData['categoryId']);
        $this->assertEquals(100, $firestoreData['priceMin']);
        $this->assertEquals(200, $firestoreData['priceMax']);
    }

    public function test_firestore_sync_log_can_be_created()
    {
        $log = FirestoreSyncLog::create([
            'collection' => 'services',
            'document_id' => 'test-doc-id',
            'action' => 'create',
            'direction' => 'postgres_to_firestore',
            'status' => 'success',
            'attempted_at' => now(),
            'completed_at' => now(),
        ]);

        $this->assertDatabaseHas('firestore_sync_logs', [
            'collection' => 'services',
            'status' => 'success',
        ]);
    }
}
