<?php

namespace App\Filament\Resources\ServiceBookings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ServiceBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_number')
                    ->required(),
                TextInput::make('customer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('provider_id')
                    ->numeric(),
                TextInput::make('service_id')
                    ->required()
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('scheduled_at')
                    ->required(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
                Textarea::make('customer_address')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('payment_method'),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                Textarea::make('customer_notes')
                    ->columnSpanFull(),
                Textarea::make('provider_notes')
                    ->columnSpanFull(),
                Textarea::make('cancellation_reason')
                    ->columnSpanFull(),
                TextInput::make('rating')
                    ->numeric(),
                Textarea::make('review')
                    ->columnSpanFull(),
            ]);
    }
}
