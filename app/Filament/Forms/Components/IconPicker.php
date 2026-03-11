<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class IconPicker extends Field
{
    protected string $view = 'filament.forms.components.icon-picker';

    protected array | Closure $iconOptions = [];

    protected int | Closure $gridColumns = 8;

    public function options(array | Closure $options): static
    {
        $this->iconOptions = $options;

        return $this;
    }

    public function gridColumns(int | Closure $gridColumns): static
    {
        $this->gridColumns = $gridColumns;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->evaluate($this->iconOptions);
    }

    public function getGridColumns(): int
    {
        return $this->evaluate($this->gridColumns);
    }
}
