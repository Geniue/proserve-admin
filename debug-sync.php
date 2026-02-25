<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$theme = App\Models\ThemeConfig::find(1);

echo "Database primary_teal: " . $theme->primary_teal . PHP_EOL;

$firestoreData = $theme->toFirestoreArray();
echo "toFirestoreArray colors.primaryTeal: " . $firestoreData['colors']['primaryTeal'] . PHP_EOL;

// Now actually push and see what happens
echo "\nPushing to Firestore...\n";
$theme->pushToFirestore('update');

echo "Done!\n";
