<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use App\Support\TransparentImageTrimmer;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    public function mount(): void
    {
        $homepage = Page::homepage()->first();

        if ($homepage) {
            $this->redirect(PageResource::getUrl('edit', ['record' => $homepage]));

            return;
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = 'home';
        $data['content_blocks'] = $data['content_blocks'] ?? Page::defaultContentBlocks();

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
                if (! empty($value)) {
                    $filePath = is_array($value) ? reset($value) : $value;
                    // Strip leading 'public/' if present to avoid double path segments
                    $filePath = preg_replace('#^public/#', '', $filePath);
                    TransparentImageTrimmer::trimPublicDiskImage($filePath);
                    $fullUrl = Storage::disk('public')->url($filePath);
                    // Store as relative path only
                    $uploadOverrides[$urlKey] = parse_url($fullUrl, PHP_URL_PATH);
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
