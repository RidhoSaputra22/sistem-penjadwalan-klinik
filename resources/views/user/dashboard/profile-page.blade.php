<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $hp = '';

    public ?string $photo = null;
    public $photoUpload;


    protected $listeners = [
        'file-uploaded' => '$restart',
    ];

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirectRoute('user.login');
            return;
        }

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->hp = (string) $user->hp;
        $this->photo = $user->photo;
    }

    public function savePhoto(): void
    {
        $userId = Auth::id();

        if (!$userId) {
            $this->redirectRoute('user.login');
            return;
        }

        $this->validate([
            'photoUpload' => ['required', 'image', 'max:2048'],
        ]);

        $user = User::query()->find($userId);

        if (!$user) {
            $this->redirectRoute('user.login');
            return;
        }

        $path = $this->photoUpload->store('users', 'public');

        if (!empty($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->update(['photo' => $path]);

        $this->photo = $path;
        $this->photoUpload = null;

        session()->flash('success', 'Foto profil berhasil diperbarui.');
    }

    #[On('file-uploaded')]
    public function handleUploadedPhoto(string $path): void
    {
        $userId = Auth::id();

        if (!$userId) {
            $this->redirectRoute('user.login');
            return;
        }

        $user = User::query()->find($userId);

        if (!$user) {
            $this->redirectRoute('user.login');
            return;
        }

        // Hapus foto lama jika ada
        if (!empty($user->photo) && $user->photo !== $path) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->update(['photo' => $path]);
        $this->photo = $path;

        // Refresh navbar component that reads auth()->user()->photo
        $this->dispatch('user-photo-updated');
    }

    public function save(): void
    {
        $userId = Auth::id();

        if (!$userId) {
            $this->redirectRoute('user.login');
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'hp' => ['required', 'string', 'max:255', Rule::unique('users', 'hp')->ignore($userId)],
        ]);

        User::query()->whereKey($userId)->update($validated);

        session()->flash('success', 'Profil berhasil diperbarui.');
    }
}; ?>


<div class="p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Profil Saya</h2>
        <p class="text-sm text-gray-500">Perbarui informasi akun Anda.</p>
    </div>

    @if (session()->has('success'))
        <div class="p-3 rounded-sm border border-green-200 bg-green-50 text-green-700 mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8 p-4 border rounded-sm" wire:loading.class="opacity-50 pointer-events-none">
        <div class="flex items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @if (!empty($photo))
                    <img src="{{ asset('storage/' . $photo) }}" alt="Foto Profil" class="w-16 h-16 rounded-full object-cover border" />
                @else
                    <div class="w-16 h-16 rounded-full border bg-gray-50"></div>
                @endif

                <div>
                    <div class="font-semibold">Foto Profil</div>
                    <div class="text-sm text-gray-500">Upload foto untuk akun Anda.</div>
                </div>
            </div>
            @livewire('user.components.upload-photo-modal', [
                'disk' => 'public',
                'directory' => 'users',
                'accept' => 'image/*',
                'maxSizeKb' => 2048,
                'imageOnly' => true,
                'returnEvent' => 'file-uploaded',
            ])
        </div>

        @error('photoUpload')
            <p class="text-red-500 text-sm font-light mt-2">{{ $message }}</p>
        @enderror
    </div>

    <form wire:submit.prevent="save" class="space-y-5" wire:loading.class="opacity-50 pointer-events-none">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @component('components.form.input', [
                'label' => 'Nama',
                'type' => 'text',
                'wireModel' => 'name',
                'placeholder' => 'Masukkan nama',
                'required' => true,
            ])
            @endcomponent

            @component('components.form.input', [
                'label' => 'Email',
                'type' => 'email',
                'wireModel' => 'email',
                'placeholder' => 'Masukkan email',
                'required' => true,
            ])
            @endcomponent

            @component('components.form.input', [
                'label' => 'No. HP',
                'type' => 'text',
                'wireModel' => 'hp',
                'placeholder' => 'Masukkan nomor HP',
                'required' => true,
            ])
            @endcomponent
        </div>

        <div>
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="bg-primary text-white px-6 py-2 rounded-sm text-sm font-semibold hover:opacity-90">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
