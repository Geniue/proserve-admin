<?php

namespace App\Filament\Resources\ServiceBookings\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Split;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class ServiceBookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Status Progress Bar
                Section::make()
                    ->schema([
                        ViewEntry::make('status_progress')
                            ->view('filament.infolists.booking-status-progress')
                            ->columnSpanFull(),
                    ]),

                Tabs::make('Booking Details')
                    ->tabs([
                        Tab::make('Overview')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        // Booking Card
                                        Section::make('Booking Information')
                                            ->icon('heroicon-o-ticket')
                                            ->schema([
                                                TextEntry::make('booking_number')
                                                    ->label('Booking #')
                                                    ->weight('bold')
                                                    ->size(TextSize::Large)
                                                    ->copyable()
                                                    ->icon('heroicon-o-hashtag'),
                                                TextEntry::make('status')
                                                    ->badge()
                                                    ->color(fn ($state) => match ($state) {
                                                        'pending' => 'warning',
                                                        'confirmed' => 'info',
                                                        'in_progress', 'started' => 'primary',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger',
                                                        'refunded' => 'warning',
                                                        'assigned', 'accepted' => 'info',
                                                        default => 'gray',
                                                    })
                                                    ->icon(fn ($state) => match ($state) {
                                                        'pending' => 'heroicon-o-clock',
                                                        'confirmed' => 'heroicon-o-check-circle',
                                                        'in_progress' => 'heroicon-o-wrench-screwdriver',
                                                        'completed' => 'heroicon-o-check-badge',
                                                        'cancelled' => 'heroicon-o-x-circle',
                                                        default => 'heroicon-o-information-circle',
                                                    }),
                                                TextEntry::make('scheduled_at')
                                                    ->label('Scheduled Date')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->icon('heroicon-o-calendar'),
                                                TextEntry::make('started_at')
                                                    ->label('Started')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->icon('heroicon-o-play')
                                                    ->placeholder('Not started'),
                                                TextEntry::make('completed_at')
                                                    ->label('Completed')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->icon('heroicon-o-flag')
                                                    ->placeholder('Not completed'),
                                                TextEntry::make('created_at')
                                                    ->dateTime('M j, Y g:i A')
                                                    ->icon('heroicon-o-calendar-days')
                                                    ->color('gray'),
                                            ])
                                            ->columnSpan(1),

                                        // Customer Card
                                        Section::make('Customer')
                                            ->icon('heroicon-o-user')
                                            ->schema([
                                                TextEntry::make('customer.name')
                                                    ->label('Name')
                                                    ->weight('bold')
                                                    ->icon('heroicon-o-user-circle'),
                                                TextEntry::make('customer.email')
                                                    ->label('Email')
                                                    ->icon('heroicon-o-envelope')
                                                    ->copyable(),
                                                TextEntry::make('customer.phone')
                                                    ->label('Phone')
                                                    ->icon('heroicon-o-phone')
                                                    ->copyable()
                                                    ->placeholder('No phone'),
                                                TextEntry::make('customer_address')
                                                    ->label('Address')
                                                    ->icon('heroicon-o-map-pin')
                                                    ->placeholder('No address'),
                                            ])
                                            ->columnSpan(1),

                                        // Service & Provider Card
                                        Section::make('Service & Provider')
                                            ->icon('heroicon-o-wrench-screwdriver')
                                            ->schema([
                                                TextEntry::make('service.name')
                                                    ->label('Service')
                                                    ->weight('bold')
                                                    ->icon('heroicon-o-briefcase')
                                                    ->badge()
                                                    ->color('primary'),
                                                TextEntry::make('service.category.name')
                                                    ->label('Category')
                                                    ->icon('heroicon-o-tag')
                                                    ->placeholder('Uncategorized'),
                                                TextEntry::make('provider.user.name')
                                                    ->label('Provider')
                                                    ->icon('heroicon-o-user-group')
                                                    ->placeholder('Not assigned')
                                                    ->weight('bold'),
                                                TextEntry::make('provider.user.phone')
                                                    ->label('Provider Phone')
                                                    ->icon('heroicon-o-phone')
                                                    ->placeholder('N/A'),
                                            ])
                                            ->columnSpan(1),
                                    ]),
                            ]),

                        Tab::make('Payment')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Payment Breakdown')
                                            ->icon('heroicon-o-calculator')
                                            ->schema([
                                                TextEntry::make('price')
                                                    ->label('Subtotal')
                                                    ->money('USD')
                                                    ->icon('heroicon-o-currency-dollar')
                                                    ->size(TextSize::Large),
                                                TextEntry::make('discount')
                                                    ->label('Discount')
                                                    ->money('USD')
                                                    ->icon('heroicon-o-receipt-percent')
                                                    ->color('success'),
                                                TextEntry::make('tax')
                                                    ->label('Tax')
                                                    ->money('USD')
                                                    ->icon('heroicon-o-receipt-percent')
                                                    ->color('gray'),
                                                TextEntry::make('total_amount')
                                                    ->label('Total Amount')
                                                    ->money('USD')
                                                    ->weight('bold')
                                                    ->size(TextSize::Large)
                                                    ->icon('heroicon-o-banknotes')
                                                    ->color('primary'),
                                            ])
                                            ->columnSpan(1),

                                        Section::make('Payment Status')
                                            ->icon('heroicon-o-credit-card')
                                            ->schema([
                                                TextEntry::make('payment_status')
                                                    ->label('Status')
                                                    ->badge()
                                                    ->size(TextSize::Large)
                                                    ->color(fn ($state) => match ($state) {
                                                        'paid' => 'success',
                                                        'pending' => 'warning',
                                                        'failed' => 'danger',
                                                        'refunded' => 'info',
                                                        default => 'gray',
                                                    })
                                                    ->icon(fn ($state) => match ($state) {
                                                        'paid' => 'heroicon-o-check-circle',
                                                        'pending' => 'heroicon-o-clock',
                                                        'failed' => 'heroicon-o-x-circle',
                                                        'refunded' => 'heroicon-o-arrow-uturn-left',
                                                        default => 'heroicon-o-question-mark-circle',
                                                    }),
                                                TextEntry::make('payment_method')
                                                    ->label('Method')
                                                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state ?? 'N/A')))
                                                    ->icon('heroicon-o-credit-card'),
                                            ])
                                            ->columnSpan(1),
                                    ]),
                            ]),

                        Tab::make('Notes & Review')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Notes')
                                            ->icon('heroicon-o-document-text')
                                            ->schema([
                                                TextEntry::make('customer_notes')
                                                    ->label('Customer Notes')
                                                    ->placeholder('No customer notes')
                                                    ->markdown(),
                                                TextEntry::make('provider_notes')
                                                    ->label('Provider Notes')
                                                    ->placeholder('No provider notes')
                                                    ->markdown(),
                                                TextEntry::make('cancellation_reason')
                                                    ->label('Cancellation Reason')
                                                    ->placeholder('N/A')
                                                    ->color('danger')
                                                    ->visible(fn ($record) => in_array($record->status, ['cancelled', 'refunded'])),
                                            ])
                                            ->columnSpan(1),

                                        Section::make('Rating & Review')
                                            ->icon('heroicon-o-star')
                                            ->schema([
                                                TextEntry::make('rating')
                                                    ->label('Rating')
                                                    ->formatStateUsing(function ($state) {
                                                        if (!$state) return 'Not rated';
                                                        $stars = str_repeat('★', $state) . str_repeat('☆', 5 - $state);
                                                        return "{$stars}  ({$state}/5)";
                                                    })
                                                    ->color(fn ($state) => match (true) {
                                                        !$state => 'gray',
                                                        $state >= 4 => 'success',
                                                        $state >= 3 => 'warning',
                                                        default => 'danger',
                                                    })
                                                    ->size(TextSize::Large),
                                                TextEntry::make('review')
                                                    ->label('Review')
                                                    ->placeholder('No review submitted')
                                                    ->markdown(),
                                            ])
                                            ->columnSpan(1),
                                    ]),
                            ]),

                        Tab::make('Tracking')
                            ->icon('heroicon-o-map')
                            ->badge(fn ($record) => $record->events()->count() ?: null)
                            ->schema([
                                Section::make('Booking Timeline')
                                    ->icon('heroicon-o-clock')
                                    ->description('Complete history of all booking events and status changes')
                                    ->schema([
                                        ViewEntry::make('timeline')
                                            ->view('filament.infolists.booking-timeline')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Sync')
                            ->icon('heroicon-o-arrow-path')
                            ->schema([
                                Section::make('Firebase Sync')
                                    ->icon('heroicon-o-cloud')
                                    ->schema([
                                        TextEntry::make('firebase_id')
                                            ->label('Firebase ID')
                                            ->copyable()
                                            ->icon('heroicon-o-finger-print')
                                            ->placeholder('Not synced'),
                                        TextEntry::make('last_synced_at')
                                            ->label('Last Synced')
                                            ->dateTime('M j, Y g:i A')
                                            ->icon('heroicon-o-arrow-path')
                                            ->since()
                                            ->placeholder('Never synced'),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString(),
            ]);
    }
}
