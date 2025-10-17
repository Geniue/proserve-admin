<?php

namespace App\Filament\Resources\AppVersions\Pages;

use App\Filament\Resources\AppVersions\AppVersionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAppVersion extends ViewRecord
{
    protected static string $resource = AppVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
