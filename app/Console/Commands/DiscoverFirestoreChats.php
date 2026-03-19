<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Firestore;

class DiscoverFirestoreChats extends Command
{
    protected $signature = 'firestore:discover-chats';
    protected $description = 'Discover chat-related collections in Firestore';

    public function handle(Firestore $firestore)
    {
        $db = $firestore->database();

        $this->info('📋 Listing all root Firestore collections...');
        $this->newLine();

        $collections = $db->collections();
        $allNames = [];

        foreach ($collections as $collection) {
            $name = $collection->id();
            $allNames[] = $name;
        }

        sort($allNames);
        $this->table(['Collection Name'], array_map(fn($n) => [$n], $allNames));

        $this->newLine();

        // Check for chat-related collections
        $chatKeywords = ['chat', 'message', 'support', 'conversation', 'inbox', 'room'];
        $chatCollections = array_filter($allNames, function ($name) use ($chatKeywords) {
            $lower = strtolower($name);
            foreach ($chatKeywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return true;
                }
            }
            return false;
        });

        if (empty($chatCollections)) {
            $this->warn('⚠️  No chat-related collections found! The Flutter app may not have created any chat conversations yet.');
            return;
        }

        $this->info('🔍 Chat-related collections found:');
        foreach ($chatCollections as $collName) {
            $this->newLine();
            $this->info("=== {$collName} ===");

            $docs = $db->collection($collName)->limit(5)->documents();
            $count = 0;
            foreach ($docs as $doc) {
                if ($doc->exists()) {
                    $count++;
                    $data = $doc->data();
                    $this->line("  Doc ID: {$doc->id()}");
                    $this->line("  Fields: " . implode(', ', array_keys($data)));

                    // Show a few key fields
                    foreach (['userName', 'userPhone', 'lastMessage', 'status', 'userId', 'email', 'displayName', 'name', 'text', 'message'] as $field) {
                        if (isset($data[$field])) {
                            $val = is_string($data[$field]) ? $data[$field] : json_encode($data[$field]);
                            $this->line("  {$field}: {$val}");
                        }
                    }

                    // Check for sub-collections
                    $subCollections = $doc->reference()->collections();
                    $subNames = [];
                    foreach ($subCollections as $subCol) {
                        $subNames[] = $subCol->id();
                    }
                    if (!empty($subNames)) {
                        $this->line("  Sub-collections: " . implode(', ', $subNames));
                    }

                    $this->newLine();
                }
            }

            if ($count === 0) {
                $this->warn("  (empty collection)");
            } else {
                $this->info("  Showed {$count} document(s)");
            }
        }
    }
}
