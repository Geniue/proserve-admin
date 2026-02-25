<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirestoreRestClient;

class DiscoverFirestoreSchema extends Command
{
    protected $signature = 'firebase:discover-schema 
                            {--collection=* : Specific collections to check}
                            {--limit=10 : Number of documents to sample per collection}';
    
    protected $description = 'Discover all Firestore collections and their document schemas';

    protected array $knownCollections = [
        // Core user/provider collections
        'users',
        'providers',
        'service_providers',
        'serviceProviders',
        
        // Service collections
        'services',
        'serviceCategories',
        'service_categories',
        'categories',
        
        // Booking collections
        'bookings',
        'appointments',
        'orders',
        
        // Review collections
        'reviews',
        'ratings',
        
        // Payment collections
        'payments',
        'transactions',
        'wallets',
        
        // Location collections
        'addresses',
        'locations',
        
        // Chat collections
        'chats',
        'conversations',
        'messages',
        
        // Notification collections
        'notifications',
        'pushNotifications',
        
        // Promo collections
        'promotions',
        'coupons',
        'discounts',
        'offers',
        
        // Content collections
        'banners',
        'sliders',
        'faqs',
        'pages',
        'content',
        
        // Config collections
        'appConfig',
        'app_config',
        'settings',
        'config',
        'configurations',
        
        // Support collections
        'supportTickets',
        'support_tickets',
        'tickets',
        
        // Availability collections
        'availability',
        'timeSlots',
        'time_slots',
        'schedules',
        
        // Other common collections
        'favorites',
        'wishlist',
        'reports',
        'analytics',
        'logs',
    ];

    public function handle()
    {
        $this->info('🔍 Discovering Firestore Schema...');
        $this->newLine();

        try {
            $firestore = new FirestoreRestClient();

            $collections = $this->option('collection');
            if (empty($collections)) {
                $collections = $this->knownCollections;
            }

            $limit = (int) $this->option('limit');
            $discoveredSchema = [];
            $foundCollections = [];
            $emptyCollections = [];
            $notFoundCollections = [];

            foreach ($collections as $collectionName) {
                $this->line("Checking: <comment>{$collectionName}</comment>");
                
                try {
                    [$rawDocuments, $nextPageToken] = $firestore->listDocuments($collectionName, $limit);
                    
                    $docCount = 0;
                    $allFields = [];
                    $sampleDocuments = [];

                    foreach ($rawDocuments as $rawDoc) {
                        $docCount++;
                        $data = FirestoreRestClient::decodeDocument($rawDoc);
                        
                        // Extract document ID from name
                        $docName = $rawDoc['name'] ?? '';
                        $docId = basename($docName);
                        
                        // Analyze each field
                        foreach ($data as $key => $value) {
                            $fieldInfo = $this->analyzeField($key, $value);
                            
                            if (!isset($allFields[$key])) {
                                $allFields[$key] = $fieldInfo;
                            } else {
                                // Merge types if different
                                if ($allFields[$key]['type'] !== $fieldInfo['type']) {
                                    $allFields[$key]['type'] = $allFields[$key]['type'] . '|' . $fieldInfo['type'];
                                }
                            }
                        }

                        // Store sample document
                        if (count($sampleDocuments) < 2) {
                            $sampleDocuments[] = [
                                'id' => $docId,
                                'data' => $this->sanitizeForDisplay($data),
                            ];
                        }
                    }

                    if ($docCount > 0) {
                        $this->info("  ✓ Found {$docCount} document(s) with " . count($allFields) . " fields");
                        $foundCollections[] = $collectionName;
                        
                        $discoveredSchema[$collectionName] = [
                            'exists' => true,
                            'document_count' => $docCount,
                            'fields' => $allFields,
                            'sample_documents' => $sampleDocuments,
                        ];
                    } else {
                        $this->warn("  ⚠ Collection exists but is empty");
                        $emptyCollections[] = $collectionName;
                        $discoveredSchema[$collectionName] = [
                            'exists' => true,
                            'document_count' => 0,
                            'fields' => [],
                        ];
                    }

                } catch (\Exception $e) {
                    // Collection doesn't exist or error
                    $notFoundCollections[] = $collectionName;
                }
            }

            $this->newLine();
            $this->info('=' . str_repeat('=', 60));
            $this->info('📊 DISCOVERY SUMMARY');
            $this->info('=' . str_repeat('=', 60));
            $this->newLine();

            // Display found collections
            if (!empty($foundCollections)) {
                $this->info("✅ Found Collections (" . count($foundCollections) . "):");
                foreach ($foundCollections as $col) {
                    $fieldCount = count($discoveredSchema[$col]['fields']);
                    $docCount = $discoveredSchema[$col]['document_count'];
                    $this->line("   • {$col} ({$docCount} docs, {$fieldCount} fields)");
                }
                $this->newLine();
            }

            // Display empty collections
            if (!empty($emptyCollections)) {
                $this->warn("⚠️  Empty Collections (" . count($emptyCollections) . "):");
                foreach ($emptyCollections as $col) {
                    $this->line("   • {$col}");
                }
                $this->newLine();
            }

            // Display detailed schema for found collections
            foreach ($foundCollections as $collectionName) {
                $this->displayCollectionSchema($collectionName, $discoveredSchema[$collectionName]);
            }

            // Save to file
            $outputPath = storage_path('firestore-schema-discovery.json');
            file_put_contents($outputPath, json_encode($discoveredSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->newLine();
            $this->info("📄 Full schema saved to: {$outputPath}");

            // Generate suggested models
            $this->newLine();
            $this->generateModelSuggestions($discoveredSchema);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to discover schema: ' . $e->getMessage());
            $this->line('File: ' . $e->getFile() . ':' . $e->getLine());
            return Command::FAILURE;
        }
    }

    protected function analyzeField(string $key, $value): array
    {
        $type = $this->getFieldType($value);
        $sample = $this->getSampleValue($value);
        $children = null;

        // If it's an object/map, analyze children
        if (is_array($value) && $this->isAssociativeArray($value)) {
            $children = [];
            foreach ($value as $childKey => $childValue) {
                $children[$childKey] = $this->analyzeField($childKey, $childValue);
            }
        }

        return [
            'type' => $type,
            'sample' => $sample,
            'children' => $children,
            'nullable' => is_null($value),
        ];
    }

    protected function getFieldType($value): string
    {
        if (is_null($value)) return 'null';
        if (is_bool($value)) return 'boolean';
        if (is_int($value)) return 'integer';
        if (is_float($value)) return 'float';
        
        if (is_array($value)) {
            if (empty($value)) return 'array';
            if ($this->isAssociativeArray($value)) {
                return 'map';
            }
            // Check array contents
            $firstItem = reset($value);
            $itemType = $this->getFieldType($firstItem);
            return "array<{$itemType}>";
        }
        
        if (is_string($value)) {
            // Detect string subtypes
            if ($this->isTimestamp($value)) return 'timestamp_string';
            if ($this->isEmail($value)) return 'email';
            if ($this->isUrl($value)) return 'url';
            if ($this->isFirebaseUid($value)) return 'firebase_uid';
            if (strlen($value) > 500) return 'long_text';
            if (strlen($value) > 255) return 'text';
            return 'string';
        }
        
        if (is_object($value)) {
            $class = get_class($value);
            if (str_contains($class, 'Timestamp')) return 'firestore_timestamp';
            if (str_contains($class, 'GeoPoint')) return 'geopoint';
            if (str_contains($class, 'DocumentReference')) return 'document_reference';
            return 'object:' . basename(str_replace('\\', '/', $class));
        }
        
        return 'unknown';
    }

    protected function getSampleValue($value): string
    {
        if (is_null($value)) return 'null';
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_array($value)) {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE);
            return strlen($json) > 100 ? substr($json, 0, 100) . '...' : $json;
        }
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
            return get_class($value);
        }
        $str = (string) $value;
        // Mask sensitive data
        if ($this->isEmail($str)) {
            return $this->maskEmail($str);
        }
        return strlen($str) > 80 ? substr($str, 0, 80) . '...' : $str;
    }

    protected function sanitizeForDisplay(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeForDisplay($value);
            } elseif (is_object($value)) {
                $sanitized[$key] = '[' . get_class($value) . ']';
            } elseif (is_string($value) && $this->isEmail($value)) {
                $sanitized[$key] = $this->maskEmail($value);
            } elseif (is_string($value) && strlen($value) > 100) {
                $sanitized[$key] = substr($value, 0, 100) . '...';
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) return '***@***.***';
        $name = $parts[0];
        $domain = $parts[1];
        $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
        return $maskedName . '@' . $domain;
    }

    protected function isAssociativeArray(array $arr): bool
    {
        if (empty($arr)) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    protected function isTimestamp(string $value): bool
    {
        // ISO 8601 format or Firebase timestamp string
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}(T|\s)\d{2}:\d{2}/', $value);
    }

    protected function isEmail(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function isUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    protected function isFirebaseUid(string $value): bool
    {
        // Firebase UIDs are typically 28 characters alphanumeric
        return (bool) preg_match('/^[a-zA-Z0-9]{20,32}$/', $value);
    }

    protected function displayCollectionSchema(string $collectionName, array $info): void
    {
        $this->newLine();
        $this->info('┌' . str_repeat('─', 60));
        $this->info("│ 📁 Collection: <comment>{$collectionName}</comment>");
        $this->info("│ Documents sampled: {$info['document_count']}");
        $this->info('├' . str_repeat('─', 60));
        
        if (!empty($info['fields'])) {
            $this->info('│ Fields:');
            $tableData = [];
            foreach ($info['fields'] as $field => $fieldInfo) {
                $tableData[] = [
                    $field,
                    $fieldInfo['type'],
                    substr($fieldInfo['sample'], 0, 40) . (strlen($fieldInfo['sample']) > 40 ? '...' : ''),
                ];
            }
            
            $this->table(
                ['Field Name', 'Type', 'Sample Value'],
                $tableData
            );

            // Show nested structure for maps
            foreach ($info['fields'] as $field => $fieldInfo) {
                if ($fieldInfo['type'] === 'map' && !empty($fieldInfo['children'])) {
                    $this->line("│");
                    $this->line("│ 📦 <comment>{$field}</comment> (nested object):");
                    foreach ($fieldInfo['children'] as $childField => $childInfo) {
                        $this->line("│    • {$childField}: {$childInfo['type']}");
                    }
                }
            }
        }
        
        $this->info('└' . str_repeat('─', 60));
    }

    protected function generateModelSuggestions(array $schema): void
    {
        $this->info('=' . str_repeat('=', 60));
        $this->info('📝 SUGGESTED LARAVEL MIGRATIONS');
        $this->info('=' . str_repeat('=', 60));

        foreach ($schema as $collection => $info) {
            if (!$info['exists'] || empty($info['fields'])) continue;

            $tableName = \Str::snake($collection);
            $this->newLine();
            $this->line("<comment>// Migration for: {$collection}</comment>");
            $this->line("Schema::create('{$tableName}', function (Blueprint \$table) {");
            $this->line("    \$table->id();");
            $this->line("    \$table->string('firebase_id')->unique();");

            foreach ($info['fields'] as $field => $fieldInfo) {
                $column = $this->suggestColumn($field, $fieldInfo);
                if ($column) {
                    $this->line("    {$column}");
                }
            }

            $this->line("    \$table->timestamp('last_synced_at')->nullable();");
            $this->line("    \$table->timestamps();");
            $this->line("});");
        }
    }

    protected function suggestColumn(string $field, array $fieldInfo): ?string
    {
        // Skip internal/meta fields
        $skipFields = ['id', 'createdAt', 'updatedAt', 'created_at', 'updated_at'];
        if (in_array($field, $skipFields)) {
            return null;
        }

        $snakeField = \Str::snake($field);
        $type = $fieldInfo['type'];

        // Handle specific field names
        if (str_ends_with($snakeField, '_id') || str_ends_with($snakeField, 'Id')) {
            return "\$table->string('{$snakeField}')->nullable(); // Reference";
        }

        return match(true) {
            $type === 'boolean' => "\$table->boolean('{$snakeField}')->default(false);",
            $type === 'integer' => "\$table->integer('{$snakeField}')->default(0);",
            $type === 'float' => "\$table->decimal('{$snakeField}', 10, 2)->default(0);",
            str_contains($type, 'timestamp') => "\$table->timestamp('{$snakeField}')->nullable();",
            $type === 'email' => "\$table->string('{$snakeField}')->nullable();",
            $type === 'url' => "\$table->text('{$snakeField}')->nullable();",
            $type === 'long_text' || $type === 'text' => "\$table->text('{$snakeField}')->nullable();",
            $type === 'string' => "\$table->string('{$snakeField}')->nullable();",
            $type === 'firebase_uid' => "\$table->string('{$snakeField}')->nullable();",
            $type === 'map' => "\$table->json('{$snakeField}')->nullable();",
            str_starts_with($type, 'array') => "\$table->json('{$snakeField}')->nullable();",
            $type === 'geopoint' => "\$table->json('{$snakeField}')->nullable(); // {lat, lng}",
            $type === 'document_reference' => "\$table->string('{$snakeField}_ref')->nullable();",
            default => "\$table->text('{$snakeField}')->nullable(); // {$type}",
        };
    }
}
