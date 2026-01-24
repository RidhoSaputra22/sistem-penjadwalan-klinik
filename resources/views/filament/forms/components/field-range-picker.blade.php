<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        state: $wire.$entangle(@js($getStatePath())),

        check(box) {
            let options = @js(array_map('strval', array_keys($getOptions())));

            let lastBoxPressed = this.state.length
                ? this.state[this.state.length - 1]
                : null;

            let currentBoxIndex = options.indexOf(box);

            if (lastBoxPressed !== null) {
                let lastBoxIndex = options.indexOf(lastBoxPressed);
                let [start, end] = [lastBoxIndex, currentBoxIndex];

                if (start <= end) {
                    for (let i = start; i <= end; i++) {
                        let boxToAdd = options[i];
                        if (!this.state.includes(boxToAdd)) {
                            this.state.push(boxToAdd);
                        }
                    }
                } else {
                    for (let i = start; i >= end; i--) {
                        let boxToRemove = options[i];
                        this.state = this.state.filter(item => item !== boxToRemove);
                    }
                }
            }
        }
    }" {{ $getExtraAttributeBag() }} class="grid grid-cols-7 justify-between w-full gap-2 select-none">
        @foreach ($getOptions() as $optionValue => $optionLabel)

        <label class="flex gap-2 w-full p-3 border border-gray-300 rounded justify-center cursor-pointer "
            :class="{ 'bg-primary-500 text-white border-primary-500': state.includes('{{ $optionValue }}') }">
            <input type="checkbox" value="{{ $optionValue }}" x-model="state" class="sr-only"
                @click="check('{{ $optionValue }}')" />
            {{ $optionLabel }}
        </label>

        @endforeach

    </div>
</x-dynamic-component>
