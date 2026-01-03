@props([
'label' => 'Submit',
'class' => '',
'wireClick' => null,

])

<div class="input-form ">
    <button type="submit" wire:click="{{ $wireClick ? $wireClick : '' }}" class="{{ $class ?? '' }}">
        <span wire:loading class="inline -block mr-2">
            @include('components.spinner', ['class' => 'inline-block mr-2 '])
        </span>
        <span wire:loading.remove>{{ $label }}</span>
    </button>
</div>
