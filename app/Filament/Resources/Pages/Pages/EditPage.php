<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = 'home';

        if (isset($data['content_blocks'])) {
            $data['content_blocks'] = $this->processImageUploads($data['content_blocks']);
        }

        return $data;
    }

    private function processImageUploads(array $blocks): array
    {
        $processed = [];
        $uploadOverrides = [];

        foreach ($blocks as $key => $value) {
            if (str_ends_with($key, '_upload')) {
                $urlKey = str_replace('_upload', '', $key);
                if (!empty($value)) {
                    $filePath = is_array($value) ? reset($value) : $value;
                    $uploadOverrides[$urlKey] = '/storage/' . $filePath;
                }
                continue;
            }

            $processed[$key] = is_array($value)
                ? $this->processImageUploads($value)
                : $value;
        }

        foreach ($uploadOverrides as $key => $url) {
            $processed[$key] = $url;
        }

        return $processed;
    }
}
