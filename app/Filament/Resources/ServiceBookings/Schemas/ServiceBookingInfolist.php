<?php

namespace App\Filament\Resources\ServiceBookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ServiceBookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('booking_number'),
                TextEntry::make('customer_id')
                    ->numeric(),
                TextEntry::make('provider_id')
                    ->numeric(),
                TextEntry::make('service_id')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('scheduled_at')
                    ->dateTime(),
                TextEntry::make('started_at')
                    ->dateTime(),
                TextEntry::make('completed_at')
                    ->dateTime(),
                TextEntry::make('latitude')
                    ->numeric(),
                TextEntry::make('longitude')
                    ->numeric(),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('discount')
                    ->numeric(),
                TextEntry::make('tax')
                    ->numeric(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('payment_method'),
                TextEntry::make('payment_status'),
                TextEntry::make('rating')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
