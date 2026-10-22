<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $username = '';
    public string $full_name = '';
    public ?string $phone = '';
    public ?string $address = '';

    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public ?string $profileSuccessMessage = null;
    public ?string $passwordSuccessMessage = null;

    public function mount(): void
    {
        $user = auth()->user()->load('profile');

        $this->username = $user->username;
        $this->full_name = $user->profile?->full_name ?? '';
        $this->phone = $user->profile?->phone ?? '';
        $this->address = $user->profile?->address ?? '';
    }

    public function updateProfile(): void
    {
        $this->profileSuccessMessage = null;

        $this->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
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

        $this->dispatch('profile-updated', name: $this->full_name);
        $this->profileSuccessMessage = 'Profil Anda berhasil diperbarui!';
    }

    public function updatePassword(): void
    {
        $this->passwordSuccessMessage = null;

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset('current_password', 'new_password', 'new_password_confirmation');
        $this->passwordSuccessMessage = 'Kata sandi Anda berhasil diubah!';
    }
}; ?>

<div class="py-8 bg-slate-950 min-h-screen text-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">
                Pengaturan Profil Anggota
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Kelola informasi identitas investigator dan kredensial akun Anda.
            </p>
        </div>

        <!-- Identity Profile Section -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
            <h2 class="text-lg font-bold text-white mb-1">
                Informasi Personal Investigator
            </h2>
            <p class="text-xs text-slate-400 mb-6">
                Data ini digunakan untuk identifikasi peminjaman unit operasional.
            </p>

            @if ($profileSuccessMessage)
                <div class="mb-6 bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 px-4 py-3 rounded-xl text-sm font-medium">
                    ✅ {{ $profileSuccessMessage }}
                </div>
            @endif

            <form wire:submit="updateProfile" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Username (ID Akun)</label>
                        <input type="text"
                               value="{{ $username }}"
                               disabled
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-400 text-sm cursor-not-allowed">
                        <span class="text-[10px] text-slate-500">Username didaftarkan oleh administrator.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Nama Lengkap *</label>
                        <input type="text"
                               wire:model="full_name"
                               required
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('full_name') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Nomor Telepon / WhatsApp</label>
                        <input type="text"
                               wire:model="phone"
                               placeholder="Contoh: 08123456789"
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('phone') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Peran Akses</label>
                        <input type="text"
                               value="Anggota Investigator"
                               disabled
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-400 text-sm cursor-not-allowed">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Alamat Domisili / Markas</label>
                    <textarea wire:model="address"
                              rows="3"
                              placeholder="Alamat lengkap tempat tinggal atau posko investigasi..."
                              class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                    @error('address') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-semibold text-xs transition shadow-md">
                        <span wire:loading.remove wire:target="updateProfile">Simpan Perubahan Profil</span>
                        <span wire:loading wire:target="updateProfile">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Security / Password Section -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
            <h2 class="text-lg font-bold text-white mb-1">
                Ganti Kata Sandi
            </h2>
            <p class="text-xs text-slate-400 mb-6">
                Pastikan akun Anda menggunakan kata sandi yang kuat dan aman.
            </p>

            @if ($passwordSuccessMessage)
                <div class="mb-6 bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 px-4 py-3 rounded-xl text-sm font-medium">
                    ✅ {{ $passwordSuccessMessage }}
                </div>
            @endif

            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Kata Sandi Saat Ini *</label>
                    <input type="password"
                           wire:model="current_password"
                           required
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('current_password') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Kata Sandi Baru *</label>
                        <input type="password"
                               wire:model="new_password"
                               required
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('new_password') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Konfirmasi Kata Sandi Baru *</label>
                        <input type="password"
                               wire:model="new_password_confirmation"
                               required
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition border border-slate-700">
                        <span wire:loading.remove wire:target="updatePassword">Perbarui Kata Sandi</span>
                        <span wire:loading wire:target="updatePassword">Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
