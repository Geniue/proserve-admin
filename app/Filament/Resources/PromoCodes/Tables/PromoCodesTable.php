<?php

namespace App\Filament\Resources\PromoCodes\Tables;

use App\Models\PromoCode;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PromoCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Code copied!'),

                TextColumn::make('description_en')
                    ->label('Description')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('target_audience')
                    ->label('Audience')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'customer' => 'info',
                        'technician' => 'warning',
                        'all' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('discount_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('discount_value')
                    ->label('Value')
                    ->formatStateUsing(function ($state, PromoCode $record) {
                        if ($record->discount_type === 'percentage') {
                            return $state . '%';
                        }
                        return $state . ' SAR';
                    }),

                TextColumn::make('total_used')
                    ->label('Usage')
                    ->formatStateUsing(function ($state, PromoCode $record) {
                        $limit = $record->total_usage_limit;
                        return $state . '/' . ($limit ?? '∞');
                    })
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('Period')
                    ->formatStateUsing(function ($state, PromoCode $record) {
                        $start = $record->starts_at?->format('M j');
                        $end = $record->expires_at?->format('M j') ?? '∞';
                        return "{$start} → {$end}";
                    })
                    ->sortable(),

                IconColumn::make('status')
                    ->label('Status')
                    ->state(function (PromoCode $record): string {
                        if (!$record->is_active) return 'inactive';
                        if ($record->starts_at > now()) return 'scheduled';
                        if ($record->expires_at && $record->expires_at < now()) return 'expired';
                        if ($record->total_usage_limit && $record->total_used >= $record->total_usage_limit) return 'depleted';
                        return 'active';
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'expired' => 'heroicon-o-x-circle',
                        'scheduled' => 'heroicon-o-clock',
                        'depleted' => 'heroicon-o-no-symbol',
                        'inactive' => 'heroicon-o-minus-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'scheduled' => 'warning',
                        'depleted' => 'danger',
                        'inactive' => 'gray',
                    }),

                TextColumn::make('last_synced_at')
                    ->label('Synced')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('target_audience')
                    ->label('Audience')
                    ->options([
                        'customer' => 'Customer',
                        'technician' => 'Technician',
                        'all' => 'All',
                    ]),

                SelectFilter::make('discount_type')
                    ->label('Discount Type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('deactivate')
                    ->icon(fn (PromoCode $record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (PromoCode $record) => $record->is_active ? 'gray' : 'success')
                    ->tooltip(fn (PromoCode $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->requiresConfirmation()
                    ->action(function (PromoCode $record) {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? 'Code activated' : 'Code deactivated')
                            ->success()
                            ->send();
                    }),
                Action::make('syncToFirestore')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->tooltip('Sync to Firestore')
                    ->requiresConfirmation()
                    ->action(function (PromoCode $record) {
                        $record->pushToFirestore('update');
                        Notification::make()
                            ->title('Sync initiated')
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
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
