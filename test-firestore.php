<?php

require __DIR__ . '/vendor/autoload.php';

echo "Testing Firestore connection...\n";

try {
    $credentialsPath = __DIR__ . '/storage/firebase-credentials.json';
    echo "Credentials: {$credentialsPath}\n";
    
    $factory = (new \Kreait\Firebase\Factory())
        ->withServiceAccount($credentialsPath);
    
    echo "✓ Created factory\n";
    
    $firestore = $factory->createFirestore();
    echo "✓ Created Firestore client\n";
    
    $db = $firestore->database();
    echo "✓ Got database\n";
    
    echo "Fetching document...\n";
    $docRef = $db->collection('users')->document('83DKAuRM5tVcnXk443ujTsGZ9iJ3');
    echo "✓ Got document reference\n";
    
    echo "Getting snapshot (this is where it fails)...\n";
    $snapshot = $docRef->snapshot();
    echo "✓ Got snapshot!\n";
    
    if ($snapshot->exists()) {
        $data = $snapshot->data();
        echo "✓ Document exists!\n";
        echo "Email: " . ($data['email'] ?? 'N/A') . "\n";
        echo "SUCCESS!\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Class: " . get_class($e) . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
