<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('category_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('short_description')
                    ->columnSpanFull(),
                TextInput::make('icon_url'),
                TextInput::make('images'),
                TextInput::make('price_min')
                    ->numeric(),
                TextInput::make('price_max')
                    ->numeric(),
                TextInput::make('price_unit'),
                TextInput::make('duration')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_featured')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('metadata'),
            ]);
    }
}
