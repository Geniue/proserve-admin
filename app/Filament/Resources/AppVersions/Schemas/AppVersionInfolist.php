<?php

namespace App\Filament\Resources\AppVersions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AppVersionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('version'),
                TextEntry::make('build_number')
                    ->numeric(),
                TextEntry::make('platform'),
                IconEntry::make('force_update')
                    ->boolean(),
                TextEntry::make('download_url'),
            ]);
    }
}
