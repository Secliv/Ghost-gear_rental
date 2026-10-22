<?php

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public bool $showForm = false;
    public ?int $editingUserId = null;

    public ?int $deletingUserId = null;
    public ?string $deletingUsername = null;

    public string $full_name = '';
    public string $username = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public bool $is_active = true;

    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    public function with(): array
    {
        $users = User::query()->with('profile')->orderBy('role')->orderBy('id')->get();

        return [
            'users' => $users,
        ];
    }

    public function openCreate(): void
    {
        $this->editingUserId = null;
        $this->full_name = '';
        $this->username = '';
        $this->email = '';
        $this->phone = '';
        $this->password = '';
        $this->is_active = true;
        $this->showForm = true;
        $this->resetValidation();
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function editUser(int $id): void
    {
        $user = User::query()->with('profile')->find($id);
        if (! $user) return;

        $this->editingUserId = $user->id;
        $this->full_name = $user->profile?->full_name ?? $user->username;
        $this->username = $user->username;
        $this->email = $user->email ?? '';
        $this->phone = $user->profile?->phone ?? '';
        $this->password = '';
        $this->is_active = (bool) $user->is_active;
        $this->showForm = true;
        $this->resetValidation();
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function cancel(): void
    {
        $this->editingUserId = null;
        $this->full_name = '';
        $this->username = '';
        $this->email = '';
        $this->phone = '';
        $this->password = '';
        $this->showForm = false;
        $this->resetValidation();
    }

    public function confirmDelete(int $id, string $username): void
    {
        $this->deletingUserId = $id;
        $this->deletingUsername = $username;
    }

    public function save(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('users', 'username')->ignore($this->editingUserId),
            ],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'password' => [$this->editingUserId ? 'nullable' : 'required', 'min:8'],
        ];

        $this->validate($rules, [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi dan harus unik.',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'password.required' => 'Password awal wajib diisi minimal 8 karakter.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        try {
            if ($this->editingUserId) {
                $user = User::query()->find($this->editingUserId);
                $userData = [
                    'username' => $this->username,
                    'email' => $this->email,
                    'is_active' => $this->is_active,
                ];
                if (! empty($this->password)) {
                    $userData['password'] = Hash::make($this->password);
                }
                $user->update($userData);

                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'full_name' => $this->full_name,
                        'phone' => $this->phone,
                    ]
                );

                $this->successMessage = "Akun {$user->username} berhasil diperbarui!";
            } else {
                $user = User::query()->create([
                    'username' => $this->username,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'role' => UserRole::Anggota,
                    'is_active' => $this->is_active,
                ]);

                $memberCode = 'AGT-' . str_pad($user->id, 3, '0', STR_PAD_LEFT);

                $user->profile()->create([
                    'member_code' => $memberCode,
                    'full_name' => $this->full_name,
                    'phone' => $this->phone,
                ]);

                $this->successMessage = "Anggota baru {$this->full_name} ({$memberCode}) berhasil didaftarkan!";
            }

            $this->cancel();
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }

    public function deleteUser(int $id): void
    {
        $user = User::query()->find($id);
        if (! $user) return;

        try {
            $name = $user->username;
            $user->delete();
            $this->successMessage = "Akun {$name} berhasil dihapus.";
            $this->deletingUserId = null;
            $this->deletingUsername = null;
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Tidak dapat menghapus anggota: ' . $e->getMessage();
        }
    }
}; ?>

<div class="space-y-6">

    <!-- Alerts -->
    @if ($successMessage)
        <div class="p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-sm flex items-center gap-3 shadow-lg">
            <span class="text-[#00FF66] font-bold">✓</span>
            <span>{{ $successMessage }}</span>
        </div>
    @endif

    @if ($errorMessage)
        <div class="p-4 rounded-xl bg-rose-950/80 border border-rose-500/50 text-rose-200 text-sm flex items-center gap-3 shadow-lg">
            <span class="text-rose-400 font-bold">⚠</span>
            <span>{{ $errorMessage }}</span>
        </div>
    @endif

    <!-- ================= TABLE VIEW (Initial Default View) ================= -->
    @if (! $showForm)
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">
                    Kelola Anggota
                </h1>
                <p class="text-sm text-slate-400">
                    Pendaftaran akun dilakukan oleh admin. Kelola data, peran, dan status anggota.
                </p>
            </div>

            <button
                type="button"
                wire:click="openCreate"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-[#00FF66] text-black hover:bg-[#00E65C] transition shadow-[0_2px_12px_rgba(0,255,102,0.25)] cursor-pointer self-start sm:self-auto"
            >
                <span class="text-sm font-bold">+</span>
                <span>Tambah Anggota</span>
            </button>
        </div>

        <!-- Table of Members -->
        <div class="bg-[#111720] border border-[#1A2533] rounded-2xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#1A2533] text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-[#131B24]">
                            <th class="py-3.5 px-6">Nama / ID</th>
                            <th class="py-3.5 px-6">Email / Telepon</th>
                            <th class="py-3.5 px-6">Peran</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6">Unit Aktif</th>
                            <th class="py-3.5 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#182330]">
                        @foreach ($users as $user)
                            @php
                                $activeCount = Loan::where('member_id', $user->id)->where('status', LoanStatus::Aktif)->count();
                                $memberCode = $user->profile?->member_code ?? ($user->isAdmin() ? 'ADM-001' : 'AGT-' . str_pad($user->id, 3, '0', STR_PAD_LEFT));
                            @endphp
                            <tr class="hover:bg-[#141B24] transition">
                                <!-- Nama / ID -->
                                <td class="py-4 px-6 font-semibold text-white whitespace-nowrap">
                                    <div>{{ $user->profile?->full_name ?? $user->username }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $memberCode }}</div>
                                </td>

                                <!-- Email / Telepon -->
                                <td class="py-4 px-6 text-slate-300">
                                    <div>{{ $user->email ?: $user->username . '@ghostgear.id' }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $user->profile?->phone ?: '-' }}</div>
                                </td>

                                <!-- Peran -->
                                <td class="py-4 px-6 text-slate-300 whitespace-nowrap">
                                    {{ $user->isAdmin() ? 'Admin' : 'Anggota' }}
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    @if ($user->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-950/70 text-emerald-400 border border-emerald-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <!-- Unit Aktif -->
                                <td class="py-4 px-6 text-slate-300 whitespace-nowrap">
                                    {{ $activeCount }} / 2
                                </td>

                                <!-- Tindakan -->
                                <td class="py-4 px-6 text-right whitespace-nowrap space-x-2">
                                    <button
                                        type="button"
                                        wire:click="editUser({{ $user->id }})"
                                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer"
                                    >
                                        Edit
                                    </button>
                                    @if (! $user->isAdmin() && $user->id !== auth()->id())
                                        <button
                                            type="button"
                                            wire:click="confirmDelete({{ $user->id }}, '{{ addslashes($user->username) }}')"
                                            class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#182330] hover:bg-rose-950/40 text-slate-300 hover:text-rose-300 border border-[#253648] hover:border-rose-800/40 transition cursor-pointer"
                                        >
                                            Hapus
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[#182330] flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-400 gap-2">
                <span>{{ $users->count() }} akun &middot; {{ $users->where('role', UserRole::Anggota)->count() }} Anggota, {{ $users->where('role', UserRole::Admin)->count() }} Admin</span>
                <span class="text-slate-500">Unit aktif = unit yang sedang dipinjam</span>
            </div>
        </div>

    <!-- ================= FORM VIEW (Triggered by Tambah or Edit) ================= -->
    @else
        <!-- Form Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">
                    {{ $editingUserId ? 'Edit Anggota' : 'Tambah Anggota' }}
                </h1>
            </div>

            <button
                type="button"
                wire:click="cancel"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer self-start sm:self-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Daftar</span>
            </button>
        </div>

        <!-- Form Card -->
        <div class="bg-[#111720] border border-[#1A2533] rounded-2xl p-6 sm:p-8 space-y-6 max-w-3xl shadow-2xl">
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Lengkap -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-300">
                            Nama Lengkap
                        </label>
                        <input
                            type="text"
                            wire:model="full_name"
                            placeholder="Contoh: Aulia Putri"
                            class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition"
                        >
                        @error('full_name')
                            <p class="text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-300">
                            Username
                        </label>
                        <input
                            type="text"
                            wire:model="username"
                            placeholder="Contoh: aulia.putri"
                            class="w-full bg-[#131B23] border {{ $errors->has('username') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-[#1D2A37]' }} rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition"
                        >
                        @error('username')
                            <p class="text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-300">
                            Email
                        </label>
                        <input
                            type="email"
                            wire:model="email"
                            placeholder="aulia.putri@email.com"
                            class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition"
                        >
                        @error('email')
                            <p class="text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- No Telepon / WhatsApp -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-300">
                            No. Telepon/WhatsApp
                        </label>
                        <input
                            type="text"
                            wire:model="phone"
                            placeholder="0812 3456 7890"
                            class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition"
                        >
                        @error('phone')
                            <p class="text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Awal -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-300">
                            Password {{ $editingUserId ? '(Opsional)' : 'Awal' }}
                        </label>
                        <input
                            type="password"
                            wire:model="password"
                            placeholder="••••••••••••"
                            class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition"
                        >
                        @error('password')
                            <p class="text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-300">
                            Status
                        </label>
                        <select
                            wire:model="is_active"
                            class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition cursor-pointer"
                        >
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3 pt-4">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 rounded-xl font-bold text-xs bg-[#00FF66] text-black hover:bg-[#00E65C] disabled:opacity-80 disabled:cursor-wait transition shadow-[0_2px_10px_rgba(0,255,102,0.2)] cursor-pointer inline-flex items-center justify-center gap-2"
                    >
                        <span wire:loading.remove wire:target="save">Simpan Anggota</span>
                        <span wire:loading.flex wire:target="save" class="items-center gap-2">
                            <svg class="animate-spin h-3.5 w-3.5 text-black shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Menyimpan...</span>
                        </span>
                    </button>
                    <button
                        type="button"
                        wire:click="cancel"
                        class="px-5 py-2.5 rounded-xl font-semibold text-xs bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer"
                    >
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- ================= CUSTOM MINIMALIST DELETE CONFIRMATION MODAL ================= -->
    @if ($deletingUserId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="$set('deletingUserId', null)"></div>
            <div class="relative bg-[#111720] border border-[#1E2B38] rounded-2xl p-6 sm:p-7 max-w-sm w-full space-y-4 shadow-2xl z-10 text-center animate-in fade-in zoom-in-95 duration-200">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 mx-auto flex items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-white tracking-tight">Hapus Anggota</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Hapus akun pengguna <strong class="text-white">'{{ $deletingUsername }}'</strong>? Seluruh data akun akan dihapus dari sistem.
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="$set('deletingUserId', null)"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="deleteUser({{ $deletingUserId }})"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white transition shadow-lg shadow-rose-900/30 cursor-pointer"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
