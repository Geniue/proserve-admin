<?php

namespace App\Filament\Resources\ThemeSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ThemeSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required(),
                Textarea::make('value')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('category'),
            ]);
    }
}
