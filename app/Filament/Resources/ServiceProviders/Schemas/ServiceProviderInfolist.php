<?php

namespace App\Filament\Resources\ServiceProviders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Schema;

class ServiceProviderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),

                TextEntry::make('user.email')
                    ->label('Email'),

                TextEntry::make('service.name')
                    ->label('Service'),

                TextEntry::make('custom_price')
                    ->label('Custom Price')
                    ->money('USD'),

                TextEntry::make('bio')
                    ->label('Bio'),

                TextEntry::make('experience_years')
                    ->label('Years of Experience')
                    ->suffix(' years'),

                TextEntry::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 3.5 => 'warning',
                        default => 'danger',
                    }),

                TextEntry::make('total_reviews')
                    ->label('Total Reviews'),

                TextEntry::make('completed_jobs')
                    ->label('Completed Jobs'),

                TextEntry::make('verification_status')
                    ->label('Verification Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                IconEntry::make('is_available')
                    ->label('Available')
                    ->boolean(),

                TextEntry::make('firebase_id')
                    ->label('Firebase ID')
                    ->copyable(),

                TextEntry::make('last_synced_at')
                    ->label('Last Synced')
                    ->dateTime(),

                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Updated')
                    ->dateTime(),
            ]);
    }
}
