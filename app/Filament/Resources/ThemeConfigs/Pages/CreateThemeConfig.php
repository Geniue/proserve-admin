<?php

namespace App\Filament\Resources\ThemeConfigs\Pages;

use App\Filament\Resources\ThemeConfigs\ThemeConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateThemeConfig extends CreateRecord
{
    protected static string $resource = ThemeConfigResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
