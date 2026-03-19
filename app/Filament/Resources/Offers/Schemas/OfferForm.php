<?php

namespace App\Filament\Resources\Offers\Schemas;

use App\Models\PromoCode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content (Bilingual)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title_en')
                            ->label('Title (EN)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('title_ar')
                            ->label('Title (AR)')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description_en')
                            ->label('Description (EN)')
                            ->rows(3),
                        Textarea::make('description_ar')
                            ->label('Description (AR)')
                            ->rows(3),
                        TextInput::make('badge_en')
                            ->label('Badge (EN)')
                            ->maxLength(100)
                            ->placeholder('e.g. NEW, HOT, FLASH'),
                        TextInput::make('badge_ar')
                            ->label('Badge (AR)')
                            ->maxLength(100),
                    ]),

                Section::make('Visual')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image_url')
                            ->label('Banner Image')
                            ->image()
                            ->directory('offers')
                            ->columnSpanFull(),
                        TextInput::make('gradient_start')
                            ->label('Gradient Start')
                            ->type('color')
                            ->placeholder('#43A196'),
                        TextInput::make('gradient_end')
                            ->label('Gradient End')
                            ->type('color')
                            ->placeholder('#4CB7B0'),
                    ]),

                Section::make('Targeting')
                    ->columns(2)
                    ->schema([
                        Select::make('target_audience')
                            ->label('Target Audience')
                            ->options([
                                'customer' => 'Customer',
                                'technician' => 'Technician',
                                'all' => 'All',
                            ])
                            ->required()
                            ->default('customer'),
                        TagsInput::make('target_services')
                            ->label('Target Services (IDs)')
                            ->placeholder('Leave empty for all services'),
                        TagsInput::make('target_cities')
                            ->label('Target Cities')
                            ->placeholder('Leave empty for all cities'),
                        TagsInput::make('target_user_ids')
                            ->label('Specific User UIDs')
                            ->placeholder('Leave empty for everyone'),
                        Toggle::make('target_new_users_only')
                            ->label('New Users Only')
                            ->default(false),
                        TextInput::make('min_orders_required')
                            ->label('Min Orders Required')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),

                Section::make('Offer Mechanics')
                    ->columns(2)
                    ->schema([
                        Select::make('offer_type')
                            ->label('Offer Type')
                            ->options([
                                'percentage_discount' => 'Percentage Discount',
                                'fixed_discount' => 'Fixed Discount',
                                'cashback' => 'Cashback',
                                'free_addon' => 'Free Add-on',
                                'bundle_deal' => 'Bundle Deal',
                                'first_order' => 'First Order',
                                'referral_bonus' => 'Referral Bonus',
                                'flash_deal' => 'Flash Deal',
                                'seasonal' => 'Seasonal',
                                'bonus_earnings' => 'Bonus Earnings (Tech)',
                                'priority_listing' => 'Priority Listing (Tech)',
                            ])
                            ->required()
                            ->reactive(),
                        TextInput::make('discount_value')
                            ->label('Discount Value')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_discount')
                            ->label('Max Discount Cap')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('min_order_value')
                            ->label('Min Order Value')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('bonus_earning_pct')
                            ->label('Bonus Earning %')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->visible(fn ($get) => $get('offer_type') === 'bonus_earnings'),
                        Select::make('promo_code_id')
                            ->label('Linked Promo Code')
                            ->relationship('promoCode', 'code')
                            ->searchable()
                            ->preload()
                            ->placeholder('None'),
                    ]),

                Section::make('Scheduling')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->required()
                            ->default(now()),
                        DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->after('starts_at'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                        TextInput::make('priority')
                            ->label('Priority')
                            ->numeric()
                            ->default(0)
                            ->helperText('Higher = shown first'),
                    ]),

                Section::make('Analytics')
                    ->columns(3)
                    ->visibleOn('edit')
                    ->schema([
                        Placeholder::make('impressions_display')
                            ->label('Impressions')
                            ->content(fn ($record) => number_format($record?->impressions ?? 0)),
                        Placeholder::make('clicks_display')
                            ->label('Clicks')
                            ->content(fn ($record) => number_format($record?->clicks ?? 0)),
                        Placeholder::make('redemptions_display')
                            ->label('Redemptions')
                            ->content(fn ($record) => number_format($record?->redemptions ?? 0)),
                    ]),
            ]);
    }
}
