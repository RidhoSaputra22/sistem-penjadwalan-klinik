@props([
    'maxWidth' => 'max-w-lg',
    // Optional identifier to target this modal when closing from outside
    'name' => null,
])


<div x-cloak x-show="isOpen"
    x-on:close-modal.window="
        const target = $event.detail?.name;
        const mine = @js($name);
        if (!target || !mine || target === mine) {
            isOpen = false;
            $dispatch('modal-closed', { name: mine });
        }
    "
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="isOpen = false; $dispatch('modal-closed', { name: @js($name) })"></div>

    <!-- Modal Box -->
    <div class="relative bg-white rounded-lg shadow-lg w-full {{ $maxWidth }} p-6"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

        {{ $slot }}

    </div>
</div>
