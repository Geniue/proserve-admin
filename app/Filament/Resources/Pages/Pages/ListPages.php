<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    public function mount(): void
    {
        $homepage = Page::firstOrCreateHomepage();

        $this->redirect(PageResource::getUrl('edit', ['record' => $homepage]));
    }
}
