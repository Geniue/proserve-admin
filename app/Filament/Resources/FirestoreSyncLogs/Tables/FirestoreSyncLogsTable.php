<?php

namespace App\Filament\Resources\FirestoreSyncLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FirestoreSyncLogsTable
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

                TextColumn::make('document_id')
                    ->label('Document ID')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->document_id),

                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        'fetch' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('direction')
                    ->label('Direction')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'firestore_to_postgres' => 'info',
                        'postgres_to_firestore' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'firestore_to_postgres' => 'Firestore → DB',
                        'postgres_to_firestore' => 'DB → Firestore',
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->error_message)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('attempted_at')
                    ->label('Attempted')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('collection')
                    ->options(fn () => \App\Models\FirestoreSyncLog::distinct()
                        ->pluck('collection', 'collection')
                        ->toArray()),

                SelectFilter::make('action')
                    ->options([
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                        'fetch' => 'Fetch',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'pending' => 'Pending',
                    ]),

                SelectFilter::make('direction')
                    ->options([
                        'firestore_to_postgres' => 'Firestore → Database',
                        'postgres_to_firestore' => 'Database → Firestore',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('attempted_at', 'desc')
            ->poll('30s');
    }
}
