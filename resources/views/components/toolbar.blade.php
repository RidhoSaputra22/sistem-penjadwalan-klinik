<div class="fixed bottom-3 right-3 flex  gap-3 z-50">
    @if ($isDebug)
        <div x-data="{
        clicked: false,
        toggle() {
            const doc = document.documentElement;
            doc.classList.toggle('outline-debugger');
            this.clicked = !this.clicked;

        }
        }" @click="toggle()"
            class=" p-4 rounded-full bg-yellow-400  flex justify-center items-center cursor-pointer z-50 shadow-lg">
            Debug CSS

        </div>

    @endif


</div>
