<?php

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Banner')
                    ->height(60)
                    ->width(120),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('link_type')
                    ->label('Link Type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'url' => 'info',
                        'service' => 'primary',
                        'category' => 'success',
                        'screen' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('button_text')
                    ->label('Button')
                    ->placeholder('-'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('start_date')
                    ->label('Start')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('End')
                    ->date()
                    ->sortable(),

                TextColumn::make('firebase_id')
                    ->label('Firebase ID')
                    ->limit(15)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
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
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order');
    }
}
