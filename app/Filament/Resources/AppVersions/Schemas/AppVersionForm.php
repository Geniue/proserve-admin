<?php

namespace App\Filament\Resources\AppVersions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AppVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('version')
                    ->required(),
                TextInput::make('build_number')
                    ->required()
                    ->numeric(),
                TextInput::make('platform')
                    ->required(),
                Toggle::make('force_update')
                    ->required(),
                Textarea::make('update_message')
                    ->columnSpanFull(),
                TextInput::make('download_url'),
            ]);
    }
}
