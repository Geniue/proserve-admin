<?php

namespace App\Filament\Resources\FeatureFlags\Pages;

use App\Filament\Resources\FeatureFlags\FeatureFlagResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFeatureFlag extends ViewRecord
{
    protected static string $resource = FeatureFlagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
