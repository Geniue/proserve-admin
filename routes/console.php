<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ImportAllFirestoreData;
use App\Jobs\SyncFirestoreChanges;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Firebase Sync Scheduled Jobs
// Full import from Firestore once daily at 2:00 AM
Schedule::job(new ImportAllFirestoreData)
    ->dailyAt('02:00')
    ->onOneServer()
    ->withoutOverlapping();

// Continuous sync for each collection every 5 minutes
Schedule::job(new SyncFirestoreChanges('users'))
    ->everyFiveMinutes()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::job(new SyncFirestoreChanges('services'))
    ->everyFiveMinutes()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::job(new SyncFirestoreChanges('serviceCategories'))
    ->everyTenMinutes()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::job(new SyncFirestoreChanges('orders'))
    ->everyFiveMinutes()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::job(new SyncFirestoreChanges('banners'))
    ->everyTenMinutes()
    ->onOneServer()
    ->withoutOverlapping();

