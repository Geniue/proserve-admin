<?php

namespace App\Filament\Resources\FirestoreSyncStatus;

use App\Filament\Resources\FirestoreSyncStatus\Pages\ListFirestoreSyncStatuses;
use App\Filament\Resources\FirestoreSyncStatus\Tables\FirestoreSyncStatusTable;
use App\Models\FirestoreSyncStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FirestoreSyncStatusResource extends Resource
{
    protected static ?string $model = FirestoreSyncStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Sync Status';

    protected static ?string $modelLabel = 'Sync Status';

    protected static ?string $pluralModelLabel = 'Sync Status';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return FirestoreSyncStatusTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFirestoreSyncStatuses::route('/'),
        ];
    }
}
