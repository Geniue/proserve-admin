<?php

namespace App\Filament\Resources\ThemeConfigs\Pages;

use App\Filament\Resources\ThemeConfigs\ThemeConfigResource;
use App\Models\ThemeChangeLog;
use App\Models\ThemeConfig;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditThemeConfig extends EditRecord
{
    protected static string $resource = ThemeConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('applyPreset')
                ->label('Apply Preset')
                ->icon('heroicon-o-paint-brush')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('preset')
                        ->label('Select Preset Theme')
                        ->options([
                            'proserve_default' => 'ProServe Default (Teal)',
                            'ocean_blue' => 'Ocean Blue',
                            'dark_mode' => 'Dark Mode',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->applyPreset($data['preset']);
                    $this->record->save();
                    $this->refreshFormData(array_keys($this->record->getAttributes()));

                    Notification::make()
                        ->title('Preset Applied')
                        ->body('The theme has been updated with the selected preset.')
                        ->success()
                        ->send();
                }),

            Action::make('syncToFirestore')
                ->label('Sync to App')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sync Theme to App')
                ->modalDescription('This will immediately update the theme in the mobile app. Users will see the changes on their next app refresh.')
                ->action(function (): void {
                    try {
                        $this->record->pushToFirestore('update');

                        Notification::make()
                            ->title('Theme Synced')
                            ->body('The theme has been synced to Firestore. Mobile app users will receive the update.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sync Failed')
                            ->body('Failed to sync theme: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('viewChangeLogs')
                ->label('Change History')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->modalHeading('Theme Change History')
                ->modalContent(function (): \Illuminate\Contracts\View\View {
                    return view('filament.pages.theme-change-logs', [
                        'logs' => $this->record->changeLogs()
                            ->with('admin')
                            ->orderByDesc('changed_at')
                            ->limit(50)
                            ->get(),
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Track changes for audit log
        $original = $this->record->getOriginal();
        $changes = [];

        foreach (ThemeConfig::getColorFields() as $field) {
            if (isset($data[$field]) && isset($original[$field]) && $data[$field] !== $original[$field]) {
                $changes[$field] = [
                    'old' => $original[$field],
                    'new' => $data[$field],
                ];
            }
        }

        // Log all changes after save
        if (!empty($changes)) {
            ThemeChangeLog::logChanges($this->record, $changes);
        }

        // Update version on save
        $data['version'] = ($this->record->version ?? 0) + 1;
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
