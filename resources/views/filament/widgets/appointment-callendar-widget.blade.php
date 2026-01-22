<x-filament-widgets::widget>
    <x-filament::section :heading="$this->getHeading()">
        {{-- Widget content --}}
        <div class="min-h-96 space-y-6 " x-data="{status: @entangle('selectedStatus')}">
            <div class="flex gap-4 ">
                @foreach ($this->statusOptions as $options)
                <div wire:click="filterByStatus('{{ $options['value'] }}')"
                    class="relative min-w-24  px-3 py-2 cursor-pointer flex items-center justify-center rounded"
                    :class="status === '{{ $options['value'] }}' ? 'bg-red-500 text-white border border-red-500' : 'hover:bg-red-500 hover:text-white border border-gray-300 text-gray-700'">
                    {{ $options['label'] }}
                    @if($this->getCountByStatus($options['value']) > 3)
                    <span
                        class="absolute -top-3 -right-2 w-6 shadow-sm flex justify-center items-center aspect-square rounded-full bg-red-500 text-center text-xs font-light text-white">+3</span>
                    @endif
                </div>
                @endforeach
                <span class="flex-1"></span>

            </div>


            <div class=" divide-y divide-gray-600 ">
                @foreach ($appointments as $key => $appointment)

                @if ($key == 0 && $this->selectedStatus == 'confirmed')
                <div class="p-1 text-xs font-light border w-full">
                    <p>Selanjutnya</p>

                </div>
                <a href="{{ route('filament.admin.resources.appointments.edit', $appointment) }}"
                    class="flex items-center px-5 py-8 gap-4">
                    <div class="space-y-2 flex-4 ">
                        <h1 class="text-xs font-semibold">#{{ $appointment->code }}</h1>
                        <div>
                            <h1 class="text-lg/tight font-medium">
                                {{ $appointment->patient->user->name }}

                            </h1>
                            <h1 class="flex font-light">
                                {{ $appointment->doctor?->name ?? 'Dokter Belum Ditentukan' }} -
                                {{ $appointment->service->name }}
                            </h1>
                        </div>
                    </div>
                    <div class="space-y-2 flex-1 text-sm text-gray-600 text-right">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-calendar-days" class="" />
                            <p>{{ $appointment->scheduled_date->format('d M Y') }} -
                                {{ $appointment->scheduled_start }}</p>
                        </div>
                        <div class="flex items-center gap-2 ">
                            <x-filament::icon icon="heroicon-o-building-office-2" class="" />
                            <p>
                                {{ $appointment->room->name ?? 'Belum Ditentukan' }}
                            </p>
                        </div>
                    </div>
                </a>
                <div class="h-4  w-full">

                </div>

                @else
                <a href="{{ route('filament.admin.resources.appointments.edit', $appointment) }}"
                    class="flex items-center px-5 py-3 gap-4">
                    <div class="space-y-2 flex-4 ">
                        <h1 class="text-xs font-semibold">#{{ $appointment->code }}</h1>
                        <div>
                            <h1 class="text-lg/tight font-medium">
                                {{ $appointment->patient->user->name }}
                            </h1>
                            <h1 class="flex font-light">
                                {{ $appointment->doctor?->name ?? 'Dokter Belum Ditentukan' }} -
                                {{ $appointment->service->name }}
                            </h1>
                        </div>
                    </div>
                    <div class="space-y-2 flex-1 text-sm text-gray-600 text-right">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-calendar-days" class="" />
                            <p>{{ $appointment->scheduled_date->format('d M Y') }} -
                                {{ $appointment->scheduled_start }}</p>
                        </div>
                        <div class="flex items-center gap-2 ">
                            <x-filament::icon icon="heroicon-o-building-office-2" class="" />
                            <p>
                                {{ $appointment->room->name ?? 'Belum Ditentukan' }}
                            </p>
                        </div>
                    </div>
                </a>

                @endif



                @endforeach
            </div>
            <x-filament::pagination :paginator="$appointments" />

        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
