<?php

namespace App\Filament\Forms\Components;

use BackedEnum;
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

        $this->formatStateUsing(static function (FieldRangePicker $component, $state) {
            $options = $component->evaluate($component->options);
            $enumClass = $component->resolveEnumClass($options);

            if (! $enumClass) {
                return $state;
            }

            if (! is_array($state)) {
                return [];
            }

            return array_values(array_map(static function ($value) {
                if ($value instanceof \BackedEnum) {
                    return (string) $value->value;
                }

                if ($value instanceof \UnitEnum) {
                    return $value->name;
                }

                return (string) $value;
            }, $state));
        });

        $this->dehydrateStateUsing(static function (FieldRangePicker $component, $state) {
            $options = $component->evaluate($component->options);
            $enumClass = $component->resolveEnumClass($options);

            if (! $enumClass) {
                return $state;
            }

            if (! is_array($state)) {
                return [];
            }

            $isBackedEnum = is_a($enumClass, BackedEnum::class, allow_string: true);
            $firstCase = $enumClass::cases()[0] ?? null;
            $isIntBacked = $isBackedEnum && ($firstCase instanceof BackedEnum) && is_int($firstCase->value);

            return array_values(array_filter(array_map(static function ($value) use ($enumClass, $isBackedEnum, $isIntBacked) {
                if ($value instanceof \UnitEnum) {
                    return $value;
                }

                if ($isBackedEnum) {
                    $backedValue = $isIntBacked
                        ? (int) $value
                        : (string) $value;

                    /** @var class-string<BackedEnum> $backedEnumClass */
                    $backedEnumClass = $enumClass;

                    /** @var BackedEnum|null $case */
                    $case = $backedEnumClass::tryFrom($backedValue);

                    return $case;
                }

                $name = (string) $value;
                $constantName = $enumClass.'::'.$name;

                return defined($constantName)
                    ? constant($constantName)
                    : null;
            }, $state)));
        });
    }

    /**
     * @return class-string<UnitEnum>|null
     */
    protected function resolveEnumClass(mixed $options): ?string
    {
        if (
            is_string($options) &&
            enum_exists($options)
        ) {
            /** @var class-string<UnitEnum> $options */
            return $options;
        }

        return null;
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

        if ($enumClass = $this->resolveEnumClass($options)) {
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

        if (! is_array($options)) {
            return [];
        }

        if (array_is_list($options)) {
            return array_reduce($options, function (array $carry, mixed $option): array {
                $carry[(string) $option] = $option;

                return $carry;
            }, []);
        }

        return $options;
    }
}
