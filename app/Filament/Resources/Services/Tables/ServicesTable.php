<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('icon_url')
                    ->label('Icon')
                    ->circular()
                    ->size(40),

                TextColumn::make('name')
                    ->label('Service Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('price_min')
                    ->label('Price Range')
                    ->formatStateUsing(fn ($record) => 
                        '$' . number_format($record->price_min, 2) . 
                        ($record->price_max > $record->price_min ? ' - $' . number_format($record->price_max, 2) : '')
                    )
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->suffix(' min')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('firebase_id')
                    ->label('Firebase ID')
                    ->limit(15)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Active'),

                TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('syncToFirestore')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->tooltip('Sync to Firestore')
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
            ->defaultSort('sort_order', 'asc');
    }
}
