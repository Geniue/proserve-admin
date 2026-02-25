<?php

namespace App\Filament\Resources\FirestoreSyncStatus\Pages;

use App\Filament\Resources\FirestoreSyncStatus\FirestoreSyncStatusResource;
use App\Jobs\ImportAllFirestoreData;
use App\Jobs\SyncFirestoreChanges;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListFirestoreSyncStatuses extends ListRecords
{
    protected static string $resource = FirestoreSyncStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncAll')
                ->label('Sync All Collections')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Sync All Collections')
                ->modalDescription('This will queue sync jobs for all collections. Jobs will run in the background.')
                ->action(function () {
                    try {
                        // Dispatch jobs to queue instead of running synchronously
                        $collections = ['users', 'services', 'orders', 'ratings', 'serviceCategories'];
                        
                        foreach ($collections as $collection) {
                            SyncFirestoreChanges::dispatch($collection);
                        }
                        
                        Notification::make()
                            ->title('Sync jobs queued')
                            ->body('Sync jobs have been queued for ' . count($collections) . ' collections. Check back shortly.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sync failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
                
            Action::make('fullImport')
                ->label('Full Import')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Full Firestore Import')
                ->modalDescription('This will import ALL data from Firestore. This runs in the background and may take several minutes. Make sure queue worker is running: php artisan queue:work')
                ->action(function () {
                    try {
                        // Dispatch to queue for background processing
                        ImportAllFirestoreData::dispatch();
                        
                        Notification::make()
                            ->title('Import job queued')
                            ->body('Full import has been queued. Run "php artisan queue:work" to process.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
                
            Action::make('syncNow')
                ->label('Quick Sync (Users)')
                ->icon('heroicon-o-bolt')
                ->color('gray')
                ->action(function () {
                    try {
                        // Run a quick sync of just users collection synchronously
                        $job = new SyncFirestoreChanges('users');
                        $job->handle();
                        
                        Notification::make()
                            ->title('Users synced')
                            ->body('Users collection has been synced from Firestore.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sync failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
