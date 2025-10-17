<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestFirebaseCredentials extends Command
{
    protected $signature = 'firebase:test-credentials';
    protected $description = 'Test Firebase credentials file';

    public function handle()
    {
        $this->info('Testing Firebase credentials...');
        $this->newLine();

        $credPath = config('firebase.projects.app.credentials');
        $this->line("Credentials path from config: {$credPath}");
        
        if (!$credPath) {
            $this->error('No credentials path configured!');
            return Command::FAILURE;
        }

        $fullPath = base_path($credPath);
        $this->line("Full path: {$fullPath}");

        if (!file_exists($fullPath)) {
            $this->error('Credentials file does not exist!');
            return Command::FAILURE;
        }

        $this->line('✓ Credentials file exists');

        $contents = file_get_contents($fullPath);
        $json = json_decode($contents, true);

        if (!$json) {
            $this->error('Failed to parse JSON!');
            $this->line('JSON error: ' . json_last_error_msg());
            return Command::FAILURE;
        }

        $this->line('✓ JSON is valid');
        $this->newLine();

        $this->info('Credentials content:');
        $this->line('  Project ID: ' . ($json['project_id'] ?? 'NOT FOUND'));
        $this->line('  Client Email: ' . ($json['client_email'] ?? 'NOT FOUND'));
        $this->line('  Private Key ID: ' . (isset($json['private_key_id']) ? substr($json['private_key_id'], 0, 20) . '...' : 'NOT FOUND'));
        $this->line('  Private Key: ' . (isset($json['private_key']) ? 'Present (length: ' . strlen($json['private_key']) . ')' : 'NOT FOUND'));
        
        $this->newLine();
        
        // Try to initialize Firebase
        try {
            $this->line('Testing Firebase initialization...');
            $firestore = app(\Kreait\Firebase\Contract\Firestore::class);
            $this->info('✓ Firebase initialized successfully!');
            
            $db = $firestore->database();
            $this->info('✓ Got database reference');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to initialize Firebase!');
            $this->line('Error: ' . $e->getMessage());
            $this->line('Class: ' . get_class($e));
            
            return Command::FAILURE;
        }
    }
}
