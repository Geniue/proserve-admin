<?php

namespace App\Filament\Resources\AppConfigs\Schemas;

use Filament\Schemas\Schema;

class AppConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
