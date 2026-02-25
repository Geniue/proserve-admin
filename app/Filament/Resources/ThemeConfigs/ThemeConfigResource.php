<?php

namespace App\Filament\Resources\ThemeConfigs;

use App\Filament\Resources\ThemeConfigs\Pages\CreateThemeConfig;
use App\Filament\Resources\ThemeConfigs\Pages\EditThemeConfig;
use App\Filament\Resources\ThemeConfigs\Pages\ListThemeConfigs;
use App\Filament\Resources\ThemeConfigs\Schemas\ThemeConfigForm;
use App\Filament\Resources\ThemeConfigs\Tables\ThemeConfigsTable;
use App\Models\ThemeConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ThemeConfigResource extends Resource
{
    protected static ?string $model = ThemeConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|UnitEnum|null $navigationGroup = 'App Configuration';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Theme Management';

    protected static ?string $modelLabel = 'Theme';

    protected static ?string $pluralModelLabel = 'Themes';

    public static function form(Schema $schema): Schema
    {
        return ThemeConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ThemeConfigsTable::configure($table);
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
            'index' => ListThemeConfigs::route('/'),
            'create' => CreateThemeConfig::route('/create'),
            'edit' => EditThemeConfig::route('/{record}/edit'),
        ];
    }
}
