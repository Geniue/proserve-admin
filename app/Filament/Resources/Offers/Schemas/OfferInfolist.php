<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OfferInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title_en')->label('Title (EN)'),
                        TextEntry::make('title_ar')->label('Title (AR)'),
                        TextEntry::make('description_en')->label('Description (EN)'),
                        TextEntry::make('description_ar')->label('Description (AR)'),
                        TextEntry::make('badge_en')->label('Badge (EN)')->placeholder('-'),
                        TextEntry::make('badge_ar')->label('Badge (AR)')->placeholder('-'),
                        ImageEntry::make('image_url')->label('Banner Image')->columnSpanFull(),
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
                        TextEntry::make('offer_type')
                            ->label('Offer Type')
                            ->badge(),
                        IconEntry::make('target_new_users_only')
                            ->label('New Users Only')
                            ->boolean(),
                        TextEntry::make('min_orders_required')
                            ->label('Min Orders Required'),
                    ]),

                Section::make('Discount')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('discount_value')->label('Discount Value'),
                        TextEntry::make('max_discount')->label('Max Discount Cap')->placeholder('-'),
                        TextEntry::make('min_order_value')->label('Min Order Value')->placeholder('-'),
                        TextEntry::make('bonus_earning_pct')->label('Bonus Earning %')->placeholder('-'),
                        TextEntry::make('promoCode.code')->label('Linked Promo Code')->placeholder('None'),
                    ]),

                Section::make('Scheduling')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('starts_at')->label('Starts At')->dateTime(),
                        TextEntry::make('expires_at')->label('Expires At')->dateTime()->placeholder('No expiry'),
                        IconEntry::make('is_active')->label('Active')->boolean(),
                        TextEntry::make('sort_order')->label('Sort Order'),
                        TextEntry::make('priority')->label('Priority'),
                    ]),

                Section::make('Analytics')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('impressions')->numeric(),
                        TextEntry::make('clicks')->numeric(),
                        TextEntry::make('redemptions')->numeric(),
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
