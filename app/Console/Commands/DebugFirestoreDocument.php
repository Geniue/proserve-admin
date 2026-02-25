<?php

namespace App\Console\Commands;

use App\Services\FirestoreRestClient;
use Illuminate\Console\Command;

class DebugFirestoreDocument extends Command
{
    protected $signature = 'firebase:debug {collection} {document}';
    protected $description = 'Debug a Firestore document';

    public function handle()
    {
        $collection = $this->argument('collection');
        $documentId = $this->argument('document');

        $client = new FirestoreRestClient();
        
        // List documents and find the one we want
        [$docs, $token] = $client->listDocuments($collection, 50);
        
        foreach ($docs as $doc) {
            $name = basename($doc['name'] ?? '');
            if ($name === $documentId) {
                $this->info("Found document: {$name}");
                $this->newLine();
                
                $decoded = FirestoreRestClient::decodeDocument($doc);
                
                // Show top-level keys
                $this->info("Top-level keys: " . implode(', ', array_keys($decoded)));
                $this->newLine();
                
                // Show colors if present
                if (isset($decoded['colors']) && is_array($decoded['colors'])) {
                    $this->info("Colors map found with " . count($decoded['colors']) . " entries:");
                    foreach ($decoded['colors'] as $key => $value) {
                        $this->line("  {$key}: {$value}");
                    }
                } else {
                    $this->warn("No 'colors' map found!");
                }
                
                // Show updatedAt
                if (isset($decoded['updatedAt'])) {
                    $this->newLine();
                    $this->info("updatedAt: " . $decoded['updatedAt']);
                }
                
                return 0;
            }
        }
        
        $this->error("Document {$documentId} not found in {$collection}");
        return 1;
    }
}
