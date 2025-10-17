<?php

namespace App\Traits;

use Kreait\Firebase\Contract\Firestore;
use App\Models\FirestoreSyncLog;
use Illuminate\Support\Facades\Log;

trait SyncToFirestore
{
    /**
     * Boot the trait and register event listeners
     */
    protected static function bootSyncToFirestore()
    {
        // Only sync if Firebase sync is enabled
        if (!config('firebase.sync_enabled', true)) {
            return;
        }

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

    /**
     * Get the Firestore collection name for this model
     */
    abstract public function getFirestoreCollection(): string;

    /**
     * Get the Firestore document ID for this model
     */
    abstract public function getFirestoreDocumentId(): string;

    /**
     * Convert model to Firestore array format
     */
    abstract public function toFirestoreArray(): array;

    /**
     * Push changes to Firestore
     */
    public function pushToFirestore(string $action)
    {
        try {
            $firestore = app(Firestore::class);
            $collection = $this->getFirestoreCollection();
            $documentId = $this->getFirestoreDocumentId();

            if ($action === 'delete') {
                $firestore->database()
                    ->collection($collection)
                    ->document($documentId)
                    ->delete();
                    
                Log::info("Deleted from Firestore: {$collection}/{$documentId}");
            } else {
                $data = array_merge($this->toFirestoreArray(), [
                    'updatedAt' => now()->toIso8601String(),
                    'updatedBy' => auth()->id() ?? 'admin',
                ]);

                $firestore->database()
                    ->collection($collection)
                    ->document($documentId)
                    ->set($data, ['merge' => true]);
                    
                Log::info("Synced to Firestore: {$collection}/{$documentId} - {$action}");
            }

            // Update last_synced_at timestamp
            if (method_exists($this, 'update') && $action !== 'delete') {
                $this->updateQuietly(['last_synced_at' => now()]);
            }

            // Log success
            FirestoreSyncLog::create([
                'collection' => $collection,
                'document_id' => $documentId,
                'action' => $action,
                'direction' => 'postgres_to_firestore',
                'status' => 'success',
                'attempted_at' => now(),
                'completed_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to sync to Firestore: {$e->getMessage()}", [
                'model' => get_class($this),
                'id' => $this->id ?? null,
                'action' => $action,
            ]);

            FirestoreSyncLog::create([
                'collection' => $this->getFirestoreCollection(),
                'document_id' => $this->getFirestoreDocumentId(),
                'action' => $action,
                'direction' => 'postgres_to_firestore',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'attempted_at' => now(),
            ]);
        }
    }

    /**
     * Force sync this model to Firestore (manual trigger)
     */
    public function forceSyncToFirestore()
    {
        $this->pushToFirestore('update');
    }
}
