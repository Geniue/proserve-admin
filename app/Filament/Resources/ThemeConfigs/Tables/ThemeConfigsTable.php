<?php

namespace App\Filament\Resources\ThemeConfigs\Tables;

use App\Models\ThemeConfig;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ThemeConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Theme Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('firebase_id')
                    ->label('Firebase ID')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Firebase ID copied')
                    ->toggleable(),

                TextColumn::make('brightness')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'light' => 'warning',
                        'dark' => 'gray',
                        default => 'primary',
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('version')
                    ->label('Ver.')
                    ->sortable()
                    ->alignCenter(),

                // Show some key colors as preview
                ColorColumn::make('primary_teal_hex')
                    ->label('Primary')
                    ->getStateUsing(fn (ThemeConfig $record) => ThemeConfig::intToHex($record->primary_teal))
                    ->copyable()
                    ->tooltip('Primary Teal'),

                ColorColumn::make('secondary_teal_hex')
                    ->label('Secondary')
                    ->getStateUsing(fn (ThemeConfig $record) => ThemeConfig::intToHex($record->secondary_teal))
                    ->copyable()
                    ->tooltip('Secondary Teal'),

                ColorColumn::make('color_background_hex')
                    ->label('Background')
                    ->getStateUsing(fn (ThemeConfig $record) => ThemeConfig::intToHex($record->color_background))
                    ->copyable()
                    ->tooltip('Background'),

                TextColumn::make('last_synced_at')
                    ->label('Last Sync')
                    ->dateTime('M d, H:i')
                    ->sortable()
                    ->color(fn (ThemeConfig $record) => 
                        $record->last_synced_at?->diffInMinutes() > 30 ? 'warning' : 'success'
                    )
                    ->placeholder('Never'),

                TextColumn::make('updatedByUser.name')
                    ->label('Updated By')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),

                SelectFilter::make('brightness')
                    ->options([
                        'light' => 'Light Mode',
                        'dark' => 'Dark Mode',
                    ]),
            ])
            ->actions([
                EditAction::make(),

                Action::make('syncToFirestore')
                    ->label('Sync')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sync to App')
                    ->modalDescription('This will push the theme to the mobile app.')
                    ->action(function (ThemeConfig $record): void {
                        try {
                            $record->pushToFirestore('update');
                            
                            Notification::make()
                                ->title('Theme Synced')
                                ->body("'{$record->name}' has been synced to Firestore.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Sync Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('setActive')
                    ->label('Set Active')
                    ->icon('heroicon-o-check')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->hidden(fn (ThemeConfig $record) => $record->is_active)
                    ->action(function (ThemeConfig $record): void {
                        // Deactivate all other themes
                        ThemeConfig::where('id', '!=', $record->id)->update(['is_active' => false]);
                        
                        // Activate this theme
                        $record->update(['is_active' => true]);

                        Notification::make()
                            ->title('Theme Activated')
                            ->body("'{$record->name}' is now the active theme.")
                            ->success()
                            ->send();
                    }),

                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (ThemeConfig $record): void {
                        $newTheme = $record->replicate();
                        $newTheme->name = $record->name . ' (Copy)';
                        $newTheme->firebase_id = $record->firebase_id . '_' . time();
                        $newTheme->is_active = false;
                        $newTheme->last_synced_at = null;
                        $newTheme->save();

                        Notification::make()
                            ->title('Theme Duplicated')
                            ->body("A copy of '{$record->name}' has been created.")
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('syncSelected')
                    ->label('Sync Selected')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            try {
                                $record->pushToFirestore('update');
                            } catch (\Exception $e) {
                                // Log error but continue
                            }
                        }

                        Notification::make()
                            ->title('Themes Synced')
                            ->body("{$records->count()} themes have been synced to Firestore.")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
