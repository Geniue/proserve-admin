<?php

namespace App\Filament\Resources\ThemeSettings;

use App\Filament\Resources\ThemeSettings\Pages\CreateThemeSetting;
use App\Filament\Resources\ThemeSettings\Pages\EditThemeSetting;
use App\Filament\Resources\ThemeSettings\Pages\ListThemeSettings;
use App\Filament\Resources\ThemeSettings\Pages\ViewThemeSetting;
use App\Filament\Resources\ThemeSettings\Schemas\ThemeSettingForm;
use App\Filament\Resources\ThemeSettings\Schemas\ThemeSettingInfolist;
use App\Filament\Resources\ThemeSettings\Tables\ThemeSettingsTable;
use App\Models\ThemeSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ThemeSettingResource extends Resource
{
    protected static ?string $model = ThemeSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ThemeSettingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ThemeSettingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ThemeSettingsTable::configure($table);
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
            'index' => ListThemeSettings::route('/'),
            'create' => CreateThemeSetting::route('/create'),
            'view' => ViewThemeSetting::route('/{record}'),
            'edit' => EditThemeSetting::route('/{record}/edit'),
        ];
    }
}
