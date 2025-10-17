<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FirestoreTransportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Nothing to bind
    }

    public function boot(): void
    {
        // Force REST transport for Firestore on Windows to avoid gRPC crashes
        // See: https://github.com/googleapis/google-cloud-php-firestore
        $transport = env('FIRESTORE_TRANSPORT', 'rest');

        // Only set when not already set by environment
        if (!getenv('GOOGLE_CLOUD_PHP_FIRESTORE_TRANSPORT')) {
            putenv('GOOGLE_CLOUD_PHP_FIRESTORE_TRANSPORT=' . $transport);
        }

        // Optional: increase default timeout for Firestore HTTP client via env/config
        if (!getenv('FIREBASE_HTTP_CLIENT_TIMEOUT')) {
            putenv('FIREBASE_HTTP_CLIENT_TIMEOUT=' . (string) env('FIREBASE_HTTP_CLIENT_TIMEOUT', 60));
        }

        // Optional: help some Windows DNS issues if gRPC ever gets used
        if (!getenv('GRPC_DNS_RESOLVER')) {
            putenv('GRPC_DNS_RESOLVER=ares');
        }
    }
}
