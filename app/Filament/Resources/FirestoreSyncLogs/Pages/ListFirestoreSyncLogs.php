<?php

namespace App\Filament\Resources\FirestoreSyncLogs\Pages;

use App\Filament\Resources\FirestoreSyncLogs\FirestoreSyncLogResource;
use Filament\Resources\Pages\ListRecords;

class ListFirestoreSyncLogs extends ListRecords
{
    protected static string $resource = FirestoreSyncLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
