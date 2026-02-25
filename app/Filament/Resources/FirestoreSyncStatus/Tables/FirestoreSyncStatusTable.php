<?php

namespace App\Filament\Resources\FirestoreSyncStatus\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class FirestoreSyncStatusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('collection')
                    ->label('Collection')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('total_documents')
                    ->label('Total Documents')
                    ->sortable()
                    ->numeric(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'running' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('last_sync_at')
                    ->label('Last Sync')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                TextColumn::make('last_document_timestamp')
                    ->label('Last Document Time')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('syncNow')
                    ->label('Sync Now')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // Dispatch sync job for this collection
                        $collection = $record->collection;
                        
                        // Update status to running
                        $record->update(['status' => 'running']);
                        
                        Notification::make()
                            ->title("Sync initiated for {$collection}")
                            ->body('The sync job has been queued.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('last_sync_at', 'desc')
            ->poll('30s');
    }
}
