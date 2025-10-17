<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;

class UsersChart extends ChartWidget
{
    protected ?string $heading = 'Users Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
