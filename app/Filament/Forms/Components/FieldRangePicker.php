<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class FieldRangePicker extends Field
{
    protected string $view = 'filament.forms.components.field-range-picker';

    protected array|Closure $options = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->default([]);
    }

    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array|Closure
    {
        return $this->evaluate($this->options);
    }
}
