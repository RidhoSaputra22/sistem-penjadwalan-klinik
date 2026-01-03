<section class=" bg-secondary w-full ">
    <div class="max-w-7xl m-auto py-24 px-10 space-y-10">
        <div class="flex-1  text-white" data-aos="fade-up">
            <h1 class="text-xl font-light ">Layanan Kami</h1>
            <p class="text-4xl font-semibold">Memastikan kesehatan anda adalah prioritas kami.</p>
        </div>
        <div class="swiper serviceSwiper h-96 flex-2 " data-aos="fade-up">
            <div class="swiper-wrapper ">
                @foreach ([1, 2, 3, 4, 5, 1, 2, 3, 4, 5] as $doctor)
                    <div class="relative swiper-slide h-96 w-full overflow-hidden
                    ">
                        <img src="{{ asset('images/logo.jpg') }}" alt=""
                            class="absolute top-0 inset-0 w-10 aspect-square m-2">
                        <div class="absolute bottom-0 w-full h-20 px-3 py-3 bg-white">

                            <h1 class="text-lg">Dental Care</h1>
                            <p class="text-sm font-light w-full truncate">Periksa kesehatan gigi anda hanya di klinik
                                goaria
                                Lorem
                                ipsum, dolor sit amet consectetur adipisicing elit. Tenetur, deleniti rerum. Illum hic
                                enim
                                modi veniam ipsum dignissimos consequatur officia fuga? Impedit voluptatum officia
                                expedita,
                                nobis ipsa, id itaque iure non sit dolores maiores? Veritatis animi, amet veniam, ad non
                                ea
                                nihil labore id aliquam, dicta quasi necessitatibus nostrum dolorem! Consequatur
                                quaerat,
                                rerum autem id repellendus asperiores labore fugit, illo harum quos placeat voluptates
                                nihil
                                dolor laborum itaque minima expedita velit nam accusantium sunt, praesentium dolorem
                                nostrum
                                magni accusamus? Praesentium nisi eius, quaerat aliquam nostrum autem ratione id numquam
                                voluptatem fugit dolorem nesciunt possimus perferendis eos reiciendis laboriosam
                                eligendi
                                distinctio!</p>

                        </div>
                        <img src="{{ asset('images/doctor-placeholder.jpg') }}" alt=""
                            class="h-full object-cover object-center">
                    </div>
                @endforeach


            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

</section>

@push('scripts')
    <script>
        const serviceSwiper = new Swiper(".serviceSwiper", {
            slidesPerView: 3,
            // centeredSlides: true,
            loop: true,
            speed: 700,
            spaceBetween: 30,

            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },



            pagination: {
                // el: ".swiper-pagination",

            },
        });
    </script>
@endpush
