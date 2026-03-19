<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromoCodeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Code Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('code')
                            ->label('Promo Code')
                            ->weight('bold')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->copyable(),
                        TextEntry::make('description_en')->label('Description (EN)')->placeholder('-'),
                        TextEntry::make('description_ar')->label('Description (AR)')->placeholder('-'),
                    ]),

                Section::make('Targeting')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('target_audience')
                            ->label('Audience')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'customer' => 'info',
                                'technician' => 'warning',
                                'all' => 'success',
                                default => 'gray',
                            }),
                        IconEntry::make('first_order_only')
                            ->label('First Order Only')
                            ->boolean(),
                    ]),

                Section::make('Discount Rules')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('discount_type')
                            ->label('Type')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'percentage' => 'info',
                                'fixed' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('discount_value')->label('Value'),
                        TextEntry::make('max_discount')->label('Max Discount Cap')->placeholder('-'),
                        TextEntry::make('min_order_value')->label('Min Order Value')->placeholder('-'),
                    ]),

                Section::make('Usage')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('total_used')
                            ->label('Times Used')
                            ->formatStateUsing(function ($state, $record) {
                                $limit = $record->total_usage_limit;
                                return $state . '/' . ($limit ?? '∞');
                            }),
                        TextEntry::make('per_user_limit')->label('Per User Limit'),
                    ]),

                Section::make('Scheduling')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('starts_at')->label('Starts At')->dateTime(),
                        TextEntry::make('expires_at')->label('Expires At')->dateTime(),
                        IconEntry::make('is_active')->label('Active')->boolean(),
                    ]),

                Section::make('Sync')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('firebase_id')->label('Firebase ID')->placeholder('Not synced'),
                        TextEntry::make('last_synced_at')->label('Last Synced')->dateTime()->placeholder('Never'),
                        TextEntry::make('created_at')->dateTime(),
                        TextEntry::make('updated_at')->dateTime(),
                    ]),
            ]);
    }
}
