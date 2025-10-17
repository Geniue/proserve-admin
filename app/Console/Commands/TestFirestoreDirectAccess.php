<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Firestore;

class TestFirestoreDirectAccess extends Command
{
    protected $signature = 'firebase:test-direct';
    protected $description = 'Test direct Firestore document access';

    public function handle()
    {
        $this->info('Testing direct Firestore access...');
        $this->newLine();

        try {
            $firestore = app(Firestore::class);
            $this->line('✓ Got Firestore instance');

            $db = $firestore->database();
            $this->line('✓ Got database reference');

            // Try to get a specific document by ID instead of querying
            $this->line('Attempting to get a specific user document...');
            
            // Use one of the user IDs from your data
            $docRef = $db->collection('users')->document('83DKAuRM5tVcnXk443ujTsGZ9iJ3');
            $this->line('✓ Got document reference');
            
            $snapshot = $docRef->snapshot();
            $this->line('✓ Got snapshot');
            
            if ($snapshot->exists()) {
                $data = $snapshot->data();
                $this->info('✓ Document exists!');
                $this->newLine();
                
                $this->line('User data:');
                $this->line('  Email: ' . ($data['email'] ?? 'N/A'));
                $this->line('  First Name: ' . ($data['firstName'] ?? $data['first_name'] ?? 'N/A'));
                $this->line('  Last Name: ' . ($data['lastName'] ?? $data['last_name'] ?? 'N/A'));
                $this->line('  User Type: ' . ($data['userType'] ?? 'N/A'));
                $this->line('  Phone: ' . ($data['phone'] ?? 'N/A'));
                
                return Command::SUCCESS;
            } else {
                $this->warn('Document does not exist');
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Failed!');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->line('Class: ' . get_class($e));
            $this->line('File: ' . $e->getFile() . ':' . $e->getLine());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
}
