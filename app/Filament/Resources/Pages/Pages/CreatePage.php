<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use Filament\Resources\Pages\CreateRecord;

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

        return $data;
    }
}
