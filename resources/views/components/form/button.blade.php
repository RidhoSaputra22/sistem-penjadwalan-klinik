@props([
'label' => 'Submit',
'class' => '',
'wireClick' => null,

])

<div class="input-form ">
    <button type="submit" wire:click="{{ $wireClick ? $wireClick : '' }}" class="{{ $class ?? '' }}">
        <span wire:loading wire:target="{{ $wireClick }}" class="inline -block mr-2">
            @include('components.spinner', ['class' => 'inline-block mr-2 '])
        </span>
        <span wire:loading.remove wire:target="{{ $wireClick }}">{{ $label }}</span>
    </button>
</div>
