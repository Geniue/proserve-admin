<?php

namespace App\Filament\Resources\FirestoreSyncLogs;

use App\Filament\Resources\FirestoreSyncLogs\Pages\ListFirestoreSyncLogs;
use App\Filament\Resources\FirestoreSyncLogs\Tables\FirestoreSyncLogsTable;
use App\Models\FirestoreSyncLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FirestoreSyncLogResource extends Resource
{
    protected static ?string $model = FirestoreSyncLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Sync Logs';

    protected static ?string $modelLabel = 'Sync Log';

    protected static ?string $pluralModelLabel = 'Sync Logs';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return FirestoreSyncLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFirestoreSyncLogs::route('/'),
        ];
    }
}
