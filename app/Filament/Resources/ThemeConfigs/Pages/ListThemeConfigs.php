<?php

namespace App\Filament\Resources\ThemeConfigs\Pages;

use App\Filament\Resources\ThemeConfigs\ThemeConfigResource;
use Filament\Resources\Pages\ListRecords;

class ListThemeConfigs extends ListRecords
{
    protected static string $resource = ThemeConfigResource::class;
}
