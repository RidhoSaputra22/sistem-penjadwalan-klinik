<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Support\Contracts\HasLabel as LabelInterface;
use Illuminate\Contracts\Support\Arrayable;
use UnitEnum;

class FieldRangePicker extends Field
{
    protected string $view = 'filament.forms.components.field-range-picker';

    /**
     * @var array<string, mixed> | Arrayable | class-string | Closure | null
     */
    protected array|Arrayable|string|Closure|null $options = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->default([]);
    }

    /**
     * @param  array<string, mixed> | Arrayable | class-string | Closure | null  $options
     */
    public function options(array|Arrayable|string|Closure|null $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        $options = $this->evaluate($this->options);

        if (
            is_string($options) &&
            enum_exists($enumClass = $options)
        ) {
            /** @var class-string<UnitEnum> $enumClass */
            if (is_a($enumClass, LabelInterface::class, allow_string: true)) {
                /** @var class-string<UnitEnum&LabelInterface> $enumClass */

                return array_reduce($enumClass::cases(), function (array $carry, LabelInterface&UnitEnum $case): array {
                    $key = $case instanceof \BackedEnum ? $case->value : $case->name;
                    $carry[$key] = $case->getLabel() ?? $case->name;

                    return $carry;
                }, []);
            }

            return array_reduce($enumClass::cases(), function (array $carry, UnitEnum $case): array {
                $key = $case instanceof \BackedEnum ? $case->value : $case->name;
                $carry[$key] = $case->name;

                return $carry;
            }, []);
        }

        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        return $options ?? [];
    }
}
