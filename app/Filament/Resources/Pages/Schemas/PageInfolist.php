<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Internal Title'),
                TextEntry::make('slug')
                    ->badge(),
                IconEntry::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextEntry::make('seo_translations.en.title')
                    ->label('SEO Title (EN)'),
                TextEntry::make('seo_translations.ar.title')
                    ->label('SEO Title (AR)'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('Last Updated'),
            ]);
    }
}
