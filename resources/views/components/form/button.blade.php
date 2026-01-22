@props([
'label' => 'Submit',
'class' => '',
'wireClick' => null,

'type' => 'submit',
'wireLoadingClass' => null,
'wireTarget' => null,

])

<div class="input-form ">
    @php
        $wireTarget = $wireTarget ?? $wireClick;
    @endphp

    <button
        type="{{ $type ?? 'submit' }}"
        @if($wireClick) wire:click="{{ $wireClick }}" @endif
        @if($wireTarget && $wireLoadingClass) wire:loading.class="{{ $wireLoadingClass }}" wire:target="{{ $wireTarget }}" @endif
        class="{{ $class ?? '' }}">
        <span wire:loading wire:target="{{ $wireTarget }}" class="inline -block mr-2">
            @include('components.spinner', ['class' => 'inline-block mr-2 '])
        </span>
        <span wire:loading.remove wire:target="{{ $wireTarget }}">{{ $label }}</span>
    </button>
</div>
