<?php

namespace App\Filament\Resources\AppVersions;

use App\Filament\Resources\AppVersions\Pages\CreateAppVersion;
use App\Filament\Resources\AppVersions\Pages\EditAppVersion;
use App\Filament\Resources\AppVersions\Pages\ListAppVersions;
use App\Filament\Resources\AppVersions\Pages\ViewAppVersion;
use App\Filament\Resources\AppVersions\Schemas\AppVersionForm;
use App\Filament\Resources\AppVersions\Schemas\AppVersionInfolist;
use App\Filament\Resources\AppVersions\Tables\AppVersionsTable;
use App\Models\AppVersion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AppVersionResource extends Resource
{
    protected static ?string $model = AppVersion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AppVersionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppVersionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppVersionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppVersions::route('/'),
            'create' => CreateAppVersion::route('/create'),
            'view' => ViewAppVersion::route('/{record}'),
            'edit' => EditAppVersion::route('/{record}/edit'),
        ];
    }
}
