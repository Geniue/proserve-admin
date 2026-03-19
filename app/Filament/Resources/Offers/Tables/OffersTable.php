<?php

namespace App\Filament\Resources\Offers\Tables;

use App\Models\Offer;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Image')
                    ->height(40)
                    ->width(40)
                    ->circular(),

                TextColumn::make('title_en')
                    ->label('Title (EN)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40),

                TextColumn::make('title_ar')
                    ->label('Title (AR)')
                    ->searchable()
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('target_audience')
                    ->label('Audience')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'customer' => 'info',
                        'technician' => 'warning',
                        'all' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('offer_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'percentage_discount' => 'primary',
                        'fixed_discount' => 'success',
                        'cashback' => 'info',
                        'flash_deal' => 'danger',
                        'first_order' => 'warning',
                        'bonus_earnings' => 'success',
                        'priority_listing' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('discount_value')
                    ->label('Discount')
                    ->formatStateUsing(function ($state, Offer $record) {
                        if ($record->offer_type === 'bonus_earnings') {
                            return $record->bonus_earning_pct . '%';
                        }
                        if (in_array($record->offer_type, ['percentage_discount', 'cashback', 'first_order'])) {
                            return ($state ?? 0) . '%';
                        }
                        return ($state ?? 0) . ' SAR';
                    }),

                TextColumn::make('promoCode.code')
                    ->label('Promo Code')
                    ->placeholder('-')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('starts_at')
                    ->label('Period')
                    ->formatStateUsing(function ($state, Offer $record) {
                        $start = $record->starts_at?->format('M j');
                        $end = $record->expires_at?->format('M j') ?? '∞';
                        return "{$start} → {$end}";
                    })
                    ->sortable(),

                IconColumn::make('status')
                    ->label('Status')
                    ->state(function (Offer $record): string {
                        if (!$record->is_active) return 'inactive';
                        if ($record->starts_at > now()) return 'scheduled';
                        if ($record->expires_at && $record->expires_at < now()) return 'expired';
                        return 'active';
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'expired' => 'heroicon-o-x-circle',
                        'scheduled' => 'heroicon-o-clock',
                        'inactive' => 'heroicon-o-minus-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'scheduled' => 'warning',
                        'inactive' => 'gray',
                    }),

                TextColumn::make('impressions')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('redemptions')
                    ->label('Redeemed')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('last_synced_at')
                    ->label('Synced')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('priority', 'desc')
            ->filters([
                SelectFilter::make('target_audience')
                    ->label('Audience')
                    ->options([
                        'customer' => 'Customer',
                        'technician' => 'Technician',
                        'all' => 'All',
                    ]),

                SelectFilter::make('offer_type')
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
                        'bonus_earnings' => 'Bonus Earnings',
                        'priority_listing' => 'Priority Listing',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggleActive')
                    ->icon(fn (Offer $record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Offer $record) => $record->is_active ? 'gray' : 'success')
                    ->tooltip(fn (Offer $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->requiresConfirmation()
                    ->action(function (Offer $record) {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? 'Offer activated' : 'Offer deactivated')
                            ->success()
                            ->send();
                    }),
                Action::make('syncToFirestore')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->tooltip('Sync to Firestore')
                    ->requiresConfirmation()
                    ->action(function (Offer $record) {
                        $record->pushToFirestore('update');
                        Notification::make()
                            ->title('Sync initiated')
                            ->success()
                            ->send();
                    }),
                Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->tooltip('Duplicate')
                    ->requiresConfirmation()
                    ->action(function (Offer $record) {
                        $new = $record->replicate(['firebase_id', 'impressions', 'clicks', 'redemptions', 'last_synced_at']);
                        $new->title_en = $record->title_en . ' (Copy)';
                        $new->starts_at = now();
                        $new->is_active = false;
                        $new->save();
                        Notification::make()
                            ->title('Offer duplicated')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('syncSelected')
                        ->label('Sync to Firestore')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->pushToFirestore('update');
                            }
                            Notification::make()
                                ->title('Batch sync initiated')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-eye')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()->title('Offers activated')->success()->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-eye-slash')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()->title('Offers deactivated')->success()->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
