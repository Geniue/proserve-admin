<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ImportAllFirestoreData;
use App\Jobs\SyncFirestoreChanges;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Firebase Sync Scheduled Jobs (use closures to avoid instantiating jobs during bootstrap)
// Full import from Firestore once daily at 2:00 AM
Schedule::call(function () {
        dispatch(new ImportAllFirestoreData());
})->name('import-all-firestore')
    ->dailyAt('02:00')
    ->onOneServer()
    ->withoutOverlapping();

// Continuous sync for each collection
Schedule::call(function () {
        dispatch(new SyncFirestoreChanges('users'));
})->name('sync-firestore-users')
    ->everyFiveMinutes()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::call(function () {
        dispatch(new SyncFirestoreChanges('services'));
})->name('sync-firestore-services')
    ->everyFiveMinutes()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::call(function () {
        dispatch(new SyncFirestoreChanges('serviceCategories'));
})->name('sync-firestore-service-categories')
    ->everyTenMinutes()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::call(function () {
        dispatch(new SyncFirestoreChanges('orders'));
})->name('sync-firestore-orders')
    ->everyFiveMinutes()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::call(function () {
        dispatch(new SyncFirestoreChanges('banners'));
})->name('sync-firestore-banners')
    ->everyTenMinutes()
    ->onOneServer()
    ->withoutOverlapping();

