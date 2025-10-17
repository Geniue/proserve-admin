<?php

namespace App\Filament\Resources\AppConfigs\Pages;

use App\Filament\Resources\AppConfigs\AppConfigResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAppConfig extends ViewRecord
{
    protected static string $resource = AppConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
