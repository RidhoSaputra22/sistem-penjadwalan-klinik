<div class="bg-white sticky top-0  z-50" x-data="{ isScroll: false }">
    <div x-on:scroll.window="isScroll = (window.pageYOffset > 10) ? true : false"
        :class="isScroll ? 'shadow-md bg-white transition-all translate-y-1 duration-300 ease-in-out' : 'transition-all  duration-300 ease-in-out'">
        <div class="  h-20 flex justify-between p-5 items-center  ">
            <div class="flex gap-15">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.jpg') }}" alt="" class="w-14 aspect-square">
                    <div class="">
                        <h1 class="text-xl font-semibold">Klinik Goaria</h1>
                        <h1 class="text-sm/tight font-light">Melayani sejak 2010</h1>
                    </div>
                </div>
                <ul class=" flex gap-5 items-center">
                    <li>
                        <a href="{{ route('guest.home.welcome') }}"
                            class="hover:text-primary transition-all duration-300 ease-in-out {{ request()->routeIs('guest.home.welcome') ? 'text-primary font-semibold' : '' }}">
                            Beranda
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('guest.doctor.search') }}"
                            class="hover:text-primary transition-all duration-300 ease-in-out {{ request()->routeIs('guest.doctor.search') ? 'text-primary font-semibold' : '' }}">
                            Cari Dokter
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="hover:text-primary transition-all duration-300 ease-in-out {{ request()->routeIs('guest.services.index') ? 'text-primary font-semibold' : '' }}">
                            Layanan Kami
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="hover:text-primary transition-all duration-300 ease-in-out {{ request()->routeIs('guest.about.index') ? 'text-primary font-semibold' : '' }}">
                            Tentang Kami
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <ul class="flex gap-5">
                    <li>
                        <a href="#" class="hover:text-primary transition-all duration-300 ease-in-out">
                            Daftar
                        </a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-primary transition-all duration-300 ease-in-out">
                            Masuk
                        </a>
                    </li>
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
