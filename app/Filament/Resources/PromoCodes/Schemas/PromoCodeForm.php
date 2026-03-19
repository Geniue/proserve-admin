<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Code Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Promo Code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->suffixAction(
                                Action::make('generateCode')
                                    ->icon('heroicon-o-arrow-path')
                                    ->tooltip('Generate Random Code')
                                    ->action(function ($set) {
                                        $set('code', strtoupper(Str::random(8)));
                                    })
                            ),
                        Textarea::make('description_en')
                            ->label('Description (EN)')
                            ->rows(2),
                        Textarea::make('description_ar')
                            ->label('Description (AR)')
                            ->rows(2),
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
                        TagsInput::make('applicable_services')
                            ->label('Applicable Services (IDs)')
                            ->placeholder('Leave empty for all services'),
                        TagsInput::make('target_user_ids')
                            ->label('Specific User UIDs')
                            ->placeholder('Leave empty for public code'),
                        Toggle::make('first_order_only')
                            ->label('First Order Only')
                            ->default(false),
                    ]),

                Section::make('Discount Rules')
                    ->columns(2)
                    ->schema([
                        Select::make('discount_type')
                            ->label('Discount Type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                            ])
                            ->required()
                            ->reactive(),
                        TextInput::make('discount_value')
                            ->label('Discount Value')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_discount')
                            ->label('Max Discount Cap')
                            ->numeric()
                            ->minValue(0)
                            ->visible(fn ($get) => $get('discount_type') === 'percentage')
                            ->helperText('Cap for percentage discounts'),
                        TextInput::make('min_order_value')
                            ->label('Min Order Value')
                            ->numeric()
                            ->minValue(0),
                    ]),

                Section::make('Limits & Scheduling')
                    ->columns(2)
                    ->schema([
                        TextInput::make('total_usage_limit')
                            ->label('Total Usage Limit')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Empty = unlimited'),
                        TextInput::make('per_user_limit')
                            ->label('Per User Limit')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->required()
                            ->default(now()),
                        DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->required()
                            ->after('starts_at'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
