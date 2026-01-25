<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    #[On('booking-updated-nav')]
    public function refreshBookingHint(): void
    {
        // no-op; triggers re-render
    }

    #[On('user-photo-updated')]
    public function refreshUserPhoto(): void
    {
        // no-op; triggers re-render
    }
};

?>


<div class="bg-white sticky top-0  z-50" x-data="{ isScroll: false }">
    <div x-on:scroll.window="isScroll = (window.pageYOffset > 10) ? true : false"
        :class="isScroll ? 'shadow-md bg-white transition-all translate-y-1 duration-300 ease-in-out' : 'transition-all  duration-300 ease-in-out'">
        <div class="  h-20 flex justify-between p-5 items-center  ">
            <div class="flex gap-15">
                <a href="{{ route('guest.home.welcome') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.jpg') }}" alt="" class="w-14 aspect-square">
                    <div class="">
                        <h1 class="text-xl font-semibold">Klinik Goaria</h1>
                        <h1 class="text-sm/tight font-light">Melayani sejak 2010</h1>
                    </div>
                </a>
                <ul class=" flex gap-5 items-center">
                    <li>
                        <a href="{{ route('guest.home.welcome') }}"
                            class="hover:text-primary transition-all duration-300 ease-in-out {{ request()->routeIs('guest.home*') ? 'text-primary font-semibold' : '' }}">
                            Beranda
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('guest.doctor.search') }}"
                            class="hover:text-primary transition-all duration-300 ease-in-out {{ request()->routeIs('guest.doctor*') ? 'text-primary font-semibold' : '' }}">
                            Cari Dokter
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('guest.service.search') }}"
                            class="hover:text-primary transition-all duration-300 ease-in-out {{ request()->routeIs('guest.service.search') ? 'text-primary font-semibold' : '' }}">
                            Layanan Kami
                        </a>
                    </li>

                </ul>
            </div>
            <div>
                <ul class="flex gap-5">
                    @auth
                    <li>
                        @livewire('layouts.components.notification')
                    </li>

                    <li class="ml-3 relative">
                        @component('components.dropdown', [
                        'align' => 'right',
                        'width' => 'min-w-sm',


                        ])
                        @slot('trigger')
                        Hello, {{ auth()->user()->name }}
                        @endslot
                        @slot('content')
                        <div>
                            <div class="border-b border-gray-400">
                                <div class="flex gap-3 items-center mb-3  mx-4 my-3">
                                    @php
                                    $navUser = auth()->user()?->fresh();
                                    @endphp
                                    @if (!empty($navUser?->photo))
                                    <img src="{{ asset('storage/' . $navUser->photo) }}" alt="Foto Profil"
                                        class="size-13 aspect-square object-cover rounded-full ">
                                    @else
                                    <div class="size-13 aspect-square rounded-full border bg-gray-50"></div>
                                    @endif
                                    <div>
                                        <h1 class="text-lg/tight font-semibold">{{ $navUser?->name ?? '' }}</h1>
                                        <span
                                            class="text-sm/tight font-light text-gray-500">{{ $navUser?->email ?? '' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class=" ">
                                <a href="{{ route('user.dashboard') }}"
                                    class="text-sm font-medium block px-4 py-4 hover:bg-gray-100">
                                    Lihat Profil
                                </a>
                                <a href="{{ route('user.dashboard', ['tab' => 'history']) }}"
                                    class="text-sm font-medium block px-4 py-4 hover:bg-gray-100">
                                    Lihat History Booking
                                </a>
                                <form method="POST" action="{{ route('user.logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="text-sm font-medium block w-full text-left px-4 py-4 hover:bg-gray-100">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>

                        @endslot
                        @endcomponent

                        <div class="absolute -bottom-12 -right-1.5 w-52" x-data="{ show: false }"
                            x-on:booking-updated-nav.window="show = true; setTimeout(() => show = false, 3000)" x-cloak
                            x-show="show" x-transition.duration.500ms>
                            <div class="relative">
                                <span
                                    class="absolute -top-3 z-20 right-2 inline-block w-0 h-0 border-solid border-t-0 border-r-[9px] border-l-[9px] border-b-[17.3px] border-l-transparent border-r-transparent border-t-transparent border-b-white">
                                </span>
                                <span
                                    class="absolute -top-3 -z-10 right-2  inline-block w-0 h-0 border-solid border-t-0 border-r-[9px] border-l-[9px] border-b-[17.3px] border-l-transparent border-r-transparent border-t-transparent border-b-gray-600">
                                </span>
                                <div
                                    class=" text-xs text-center px-3 py-2 bg-white border border-gray-300 rounded-md shadow-md">
                                    Lihat Booking Kamu disini
                                </div>
                            </div>

                        </div>
                    </li>



                    @else
                    <li>
                        <button
                            class="px-5 py-2 text-sm rounded-md bg-primary text-white font-medium hover:bg-primary/80 transition-all duration-300 ease-in-out"
                            x-on:click="$dispatch('open-auth-modal', { tab: 'login' })">
                            Masuk
                        </button>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>

        @if (request()->routeIs('guest.home.welcome'))
        <div class="w-full h-1 -mt-1 bg-transparent" x-data="{
            progress: 0,
            update() {
                const doc = document.documentElement;
                const scrollTop = doc.scrollTop || document.body.scrollTop;
                const scrollHeight = doc.scrollHeight || document.body.scrollHeight;
                const clientHeight = doc.clientHeight || window.innerHeight;
                const max = Math.max(1, scrollHeight - clientHeight);
                this.progress = Math.min(100, Math.max(0, (scrollTop / max) * 100));
            }
        }" x-cloak x-init="update()" x-on:scroll.window="update()" x-on:resize.window="update()">
            <div class="h-1 bg-primary transition-[width] duration-100 ease-linear" :style="`width: ${progress}%`">
            </div>
        </div>

        @endif
    </div>

</div>