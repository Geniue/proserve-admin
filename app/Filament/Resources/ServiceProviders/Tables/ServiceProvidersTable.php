<?php

namespace App\Filament\Resources\ServiceProviders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ServiceProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Provider')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('service.name')
                    ->label('Service')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('custom_price')
                    ->label('Price')
                    ->money('USD')
                    ->placeholder('Default')
                    ->sortable(),

                TextColumn::make('experience_years')
                    ->label('Experience')
                    ->suffix(' yrs')
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 3.5 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('completed_jobs')
                    ->label('Jobs')
                    ->sortable(),

                TextColumn::make('verification_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean(),

                TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('verification_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),

                TernaryFilter::make('is_available')
                    ->label('Availability'),

                SelectFilter::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Service'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->verification_status === 'pending')
                    ->action(function ($record) {
                        $record->update(['verification_status' => 'verified']);
                        Notification::make()
                            ->title('Provider verified successfully')
                            ->success()
                            ->send();
                    }),
                Action::make('syncToFirestore')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        if (method_exists($record, 'pushToFirestore')) {
                            $record->pushToFirestore('update');
                            Notification::make()
                                ->title('Sync initiated')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
