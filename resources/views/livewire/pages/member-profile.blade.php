<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public bool $isEditing = false;

    public $photo;
    public ?string $avatar = null;

    public string $username = '';
    public string $full_name = '';
    public string $email = '';
    public ?string $phone = '';
    public ?string $address = '';
    public ?string $member_code = '';

    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public ?string $successMessage = null;

    public function mount(): void
    {
        $user = auth()->user()->load('profile');

        $this->username = $user->username;
        $this->email = $user->email ?? $user->username . '@ghostgear.id';
        $this->full_name = $user->profile?->full_name ?? $user->username;
        $this->phone = $user->profile?->phone ?? '';
        $this->address = $user->profile?->address ?? '';
        $this->member_code = $user->profile?->member_code ?? ('AGT-' . str_pad($user->id, 3, '0', STR_PAD_LEFT));
        $this->avatar = $user->profile?->avatar;

        if (request()->routeIs('member.profile.edit')) {
            $this->isEditing = true;
        }
    }

    public function toggleEdit(bool $state): void
    {
        $this->isEditing = $state;
        $this->photo = null;
        $this->successMessage = null;
        $this->resetValidation();
    }

    public function updateProfile(): void
    {
        $this->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user = auth()->user();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $this->full_name,
                'phone' => $this->phone,
                'address' => $this->address,
            ]
        );

        $this->successMessage = 'Profil Anda berhasil diperbarui!';
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'min:8', 'same:new_password_confirmation'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'new_password.same' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->successMessage = 'Password berhasil diperbarui!';
    }

    public function saveProfile(): void
    {
        $this->successMessage = null;

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'new_password' => ['nullable', 'min:8', 'same:new_password_confirmation'],
        ];

        if ($this->photo) {
            $rules['photo'] = ['image', 'max:2048'];
        }

        $this->validate($rules, [
            'full_name.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi',
            'new_password.same' => 'Konfirmasi password baru tidak cocok',
            'new_password.min' => 'Password minimal 8 karakter',
            'photo.image' => 'File foto harus berupa gambar',
            'photo.max' => 'Ukuran foto maksimal 2MB',
        ]);

        $user = auth()->user();

        $user->update([
            'email' => $this->email,
        ]);

        if (! empty($this->new_password)) {
            $user->update([
                'password' => Hash::make($this->new_password),
            ]);
            $this->new_password = '';
            $this->new_password_confirmation = '';
        }

        if ($this->photo) {
            $path = $this->photo->store('avatars', 'public');
            $this->avatar = $path;
            $this->photo = null;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $this->full_name,
                'phone' => $this->phone,
                'address' => $this->address,
                'member_code' => $this->member_code,
                'avatar' => $this->avatar,
            ]
        );

        $this->isEditing = false;
        $this->successMessage = 'Profil Anda berhasil diperbarui!';
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Title and Subtitle -->
    <div>
        <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">
            {{ $isEditing ? 'Edit Profil Saya' : 'Profil Saya' }}
        </h1>
        <p class="text-sm text-slate-400">
            {{ $isEditing ? 'Perbarui informasi pribadi dan kontak Anda.' : 'Kelola informasi pribadi dan kontak Anda.' }}
        </p>
    </div>

    @if ($successMessage)
        <div class="p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-sm flex items-center gap-3 shadow-lg">
            <span class="text-[#00FF66] font-bold">✓</span>
            <span>{{ $successMessage }}</span>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-[#111720] border border-[#1A2533] rounded-2xl p-6 sm:p-8 space-y-8 shadow-2xl">

        <!-- ================= VIEW MODE ================= -->
        @if (! $isEditing)
            <!-- User Header -->
            <div class="flex items-center justify-between pb-6 border-b border-[#182330]">
                <div class="flex items-center gap-5">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-[#17222D] border border-[#223344] overflow-hidden flex items-center justify-center p-2 shadow-inner shrink-0">
                        @if ($avatar)
                            <img src="{{ asset('storage/' . $avatar) }}" alt="Avatar" class="w-full h-full object-cover rounded-xl">
                        @else
                            <img src="{{ asset('images/logo-icon.png') }}" alt="Avatar" class="w-14 h-14 sm:w-16 sm:h-16 object-contain">
                        @endif
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-white">{{ $full_name }}</h2>
                        <p class="text-xs text-slate-400 mt-1 font-mono">
                            {{ $member_code }} &middot; Anggota sejak {{ auth()->user()->created_at->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Read-Only Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <!-- Nama Lengkap -->
                <div class="space-y-1.5">
                    <label class="block text-slate-400 font-medium">Nama lengkap</label>
                    <div class="bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-white text-sm">
                        {{ $full_name }}
                    </div>
                </div>

                <!-- Nomor Telepon -->
                <div class="space-y-1.5">
                    <label class="block text-slate-400 font-medium">Nomor telepon</label>
                    <div class="bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-white text-sm">
                        {{ $phone ?: '-' }}
                    </div>
                </div>

                <!-- Email (Full width) -->
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-slate-400 font-medium">Email</label>
                    <div class="bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-white text-sm">
                        {{ $email }}
                    </div>
                </div>

                <!-- ID Anggota (Locked) -->
                <div class="space-y-1.5">
                    <label class="block text-slate-400 font-medium">ID anggota</label>
                    <div class="bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-slate-300 flex items-center justify-between text-sm">
                        <span>{{ $member_code }}</span>
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                </div>

                <!-- Peran (Locked) -->
                <div class="space-y-1.5">
                    <label class="block text-slate-400 font-medium">Peran</label>
                    <div class="bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-slate-300 flex items-center justify-between text-sm">
                        <span>Anggota</span>
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Edit Button -->
            <div class="pt-2">
                <button
                    type="button"
                    wire:click="toggleEdit(true)"
                    class="px-6 py-2.5 rounded-xl font-bold text-xs bg-[#00FF66] text-black hover:bg-[#00E65C] transition shadow-[0_2px_10px_rgba(0,255,102,0.2)] cursor-pointer"
                >
                    Ubah Profil
                </button>
            </div>

        <!-- ================= EDIT MODE ================= -->
        @else
            <!-- Edit Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#182330]">
                <div class="flex items-center gap-5">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-[#17222D] border border-[#223344] overflow-hidden flex items-center justify-center p-2 shadow-inner shrink-0 relative">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover rounded-xl">
                        @elseif ($avatar)
                            <img src="{{ asset('storage/' . $avatar) }}" alt="Avatar" class="w-full h-full object-cover rounded-xl">
                        @else
                            <img src="{{ asset('images/logo-icon.png') }}" alt="Avatar" class="w-14 h-14 sm:w-16 sm:h-16 object-contain">
                        @endif
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-white">{{ $full_name }}</h2>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $member_code }} &middot; Anggota</p>
                        @error('photo')
                            <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Ubah Foto Profil Action -->
                <div>
                    <label class="px-4 py-2.5 rounded-xl bg-[#182330] hover:bg-[#202E3F] text-xs font-semibold text-slate-200 border border-[#253648] transition cursor-pointer inline-flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#00FF66]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Ubah Foto Profil</span>
                        <input type="file" wire:model="photo" accept="image/*" class="hidden">
                    </label>
                </div>
            </div>

            <!-- Edit Form -->
            <form wire:submit="saveProfile" class="space-y-6 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Lengkap -->
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-medium">Nama Lengkap</label>
                        <input
                            type="text"
                            wire:model="full_name"
                            class="w-full bg-[#131B23] border {{ $errors->has('full_name') ? 'border-rose-500' : 'border-[#1D2A37]' }} rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#00FF66] transition"
                        >
                        @error('full_name')
                            <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Username (Locked) - Consistent Styling -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center">
                            <label class="block text-slate-300 font-medium">Username</label>
                            <span class="text-[10px] text-slate-500">Tidak dapat diubah</span>
                        </div>
                        <div class="relative">
                            <input
                                type="text"
                                value="{{ $username }}"
                                disabled
                                class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-sm text-slate-400 cursor-not-allowed focus:outline-none"
                            >
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-medium">Email</label>
                        <input
                            type="email"
                            wire:model="email"
                            class="w-full bg-[#131B23] border {{ $errors->has('email') ? 'border-rose-500' : 'border-[#1D2A37]' }} rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#00FF66] transition"
                        >
                        @error('email')
                            <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- No. Telepon / WhatsApp -->
                    <div class="space-y-1.5">
                        <label class="block text-slate-300 font-medium">No. Telepon/WhatsApp</label>
                        <input
                            type="text"
                            wire:model="phone"
                            placeholder="Contoh: 0812 3456 7890"
                            class="w-full bg-[#131B23] border {{ $errors->has('phone') ? 'border-rose-500' : 'border-[#1D2A37]' }} rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#00FF66] transition"
                        >
                        @error('phone')
                            <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Ubah Password Section -->
                <div class="pt-6 border-t border-[#182330] space-y-4">
                    <div>
                        <h3 class="text-sm font-bold text-white">Ubah Password</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-slate-300 font-medium">Password Baru</label>
                            <input
                                type="password"
                                wire:model="new_password"
                                placeholder="••••••••••••"
                                class="w-full bg-[#131B23] border {{ $errors->has('new_password') ? 'border-rose-500' : 'border-[#1D2A37]' }} rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#00FF66] transition"
                            >
                            @error('new_password')
                                <p class="text-[11px] text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-slate-300 font-medium">Konfirmasi Password</label>
                            <input
                                type="password"
                                wire:model="new_password_confirmation"
                                placeholder="••••••••••••"
                                class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#00FF66] transition"
                            >
                        </div>
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="flex items-center gap-3 pt-4">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 rounded-xl font-bold text-xs bg-[#00FF66] text-black hover:bg-[#00E65C] disabled:opacity-80 disabled:cursor-wait transition shadow-[0_2px_10px_rgba(0,255,102,0.2)] cursor-pointer inline-flex items-center justify-center gap-2"
                    >
                        <span wire:loading.remove wire:target="saveProfile">Simpan Perubahan</span>
                        <span wire:loading.flex wire:target="saveProfile" class="items-center gap-2">
                            <svg class="animate-spin h-3.5 w-3.5 text-black shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Menyimpan...</span>
                        </span>
                    </button>
                    <button
                        type="button"
                        wire:click="toggleEdit(false)"
                        class="px-5 py-2.5 rounded-xl font-semibold text-xs bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer"
                    >
                        Batal
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
