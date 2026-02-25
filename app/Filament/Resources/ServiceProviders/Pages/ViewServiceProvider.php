<?php

namespace App\Filament\Resources\ServiceProviders\Pages;

use App\Filament\Resources\ServiceProviders\ServiceProviderResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceProvider extends ViewRecord
{
    protected static string $resource = ServiceProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
