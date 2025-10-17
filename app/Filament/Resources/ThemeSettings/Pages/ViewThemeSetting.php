<?php

namespace App\Filament\Resources\ThemeSettings\Pages;

use App\Filament\Resources\ThemeSettings\ThemeSettingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewThemeSetting extends ViewRecord
{
    protected static string $resource = ThemeSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
