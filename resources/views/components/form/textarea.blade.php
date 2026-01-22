@props([
    'wireModel' => null,
    'label' => null,
    'cols' => 30,
    'rows' => 4,
    'class' => '',
    'value' => null,

    'placeholder' => '',
    'required' => false,
    'disabled' => false,

])

<div class="input-form {{ $class }}">
    @if($label)
        <label for="{{ $wireModel }}">{{ $label }}</label>
    @endif

    <textarea
        {{ $attributes->merge([
            'name' => $wireModel,
            'id' => $wireModel,
            'cols' => $cols,
            'rows' => $rows,
            'placeholder' => $placeholder ?? '',
        ]) }}
        wire:model="{{ $wireModel }}"
        class="{{ isset($disabled) && $disabled ? 'bg-gray-200! cursor-not-allowed' : '' }}"
        {{ isset($required) && $required ? 'required' : '' }} {{ isset($disabled) && $disabled ? 'readonly' : '' }}
    ></textarea>

    @error($wireModel)
    <p class="text-red-500 text-sm font-light">{{ $message }}</p>
    @enderror
</div>
