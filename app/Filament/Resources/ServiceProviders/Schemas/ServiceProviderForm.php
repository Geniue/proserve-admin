<?php

namespace App\Filament\Resources\ServiceProviders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('service_id')
                    ->label('Service')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('custom_price')
                    ->label('Custom Price')
                    ->numeric()
                    ->prefix('$'),

                Textarea::make('bio')
                    ->label('Bio / Description')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('experience_years')
                    ->label('Years of Experience')
                    ->numeric()
                    ->default(0),

                TextInput::make('rating')
                    ->label('Rating')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(5)
                    ->step(0.1),

                TextInput::make('total_reviews')
                    ->label('Total Reviews')
                    ->numeric()
                    ->default(0),

                TextInput::make('completed_jobs')
                    ->label('Completed Jobs')
                    ->numeric()
                    ->default(0),

                Select::make('verification_status')
                    ->label('Verification Status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),

                Toggle::make('is_available')
                    ->label('Available for Bookings')
                    ->default(true),

                TextInput::make('firebase_id')
                    ->label('Firebase ID')
                    ->disabled(),
            ]);
    }
}
