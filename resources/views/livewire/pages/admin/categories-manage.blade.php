<?php

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public bool $showForm = false;
    public ?int $editingCategoryId = null;

    public ?int $deletingCategoryId = null;
    public ?string $deletingCategoryName = null;

    public string $name = '';
    public string $description = '';

    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    public function with(): array
    {
        return [
            'categories' => Category::query()->withCount('equipments')->orderBy('name')->get(),
        ];
    }

    public function openCreate(): void
    {
        $this->editingCategoryId = null;
        $this->name = '';
        $this->description = '';
        $this->showForm = true;
        $this->resetValidation();
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function editCategory(int $id): void
    {
        $cat = Category::query()->find($id);
        if (! $cat) return;

        $this->editingCategoryId = $cat->id;
        $this->name = $cat->name;
        $this->description = $cat->description ?? '';
        $this->showForm = true;
        $this->resetValidation();
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function cancel(): void
    {
        $this->name = '';
        $this->description = '';
        $this->editingCategoryId = null;
        $this->showForm = false;
        $this->resetValidation();
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deletingCategoryId = $id;
        $this->deletingCategoryName = $name;
    }

    public function save(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->editingCategoryId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama kategori wajib diisi',
            'name.unique' => 'Nama kategori sudah digunakan',
        ]);

        try {
            if ($this->editingCategoryId) {
                $category = Category::query()->find($this->editingCategoryId);
                $category->update([
                    'name' => $this->name,
                    'description' => $this->description,
                ]);
                $this->successMessage = "Kategori '{$this->name}' berhasil diperbarui!";
            } else {
                Category::query()->create([
                    'name' => $this->name,
                    'description' => $this->description,
                ]);
                $this->successMessage = "Kategori '{$this->name}' berhasil ditambahkan!";
            }

            $this->cancel();
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menyimpan kategori: ' . $e->getMessage();
        }
    }

    public function deleteCategory(int $id): void
    {
        $category = Category::query()->find($id);
        if (! $category) return;

        try {
            $name = $category->name;
            $category->equipments()->detach();
            $category->delete();
            $this->successMessage = "Kategori '{$name}' berhasil dihapus.";
            $this->deletingCategoryId = null;
            $this->deletingCategoryName = null;
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menghapus kategori: ' . $e->getMessage();
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
                    Kelola Kategori
                </h1>
                <p class="text-sm text-slate-400">
                    Atur pengelompokan alat agar katalog tetap terstruktur.
                </p>
            </div>

            <button
                type="button"
                wire:click="openCreate"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-[#00FF66] text-black hover:bg-[#00E65C] transition shadow-[0_2px_12px_rgba(0,255,102,0.25)] cursor-pointer self-start sm:self-auto"
            >
                <span class="text-sm font-bold">+</span>
                <span>Tambah Kategori</span>
            </button>
        </div>

        <!-- Table of Categories -->
        <div class="bg-[#111720] border border-[#1A2533] rounded-2xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#1A2533] text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-[#131B24]">
                            <th class="py-3.5 px-6">Kategori</th>
                            <th class="py-3.5 px-6">Deskripsi</th>
                            <th class="py-3.5 px-6">Jumlah Alat</th>
                            <th class="py-3.5 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#182330]">
                        @forelse ($categories as $cat)
                            <tr class="hover:bg-[#141B24] transition">
                                <td class="py-4 px-6 font-semibold text-white whitespace-nowrap">
                                    {{ $cat->name }}
                                </td>
                                <td class="py-4 px-6 text-slate-400">
                                    {{ $cat->description ?: '-' }}
                                </td>
                                <td class="py-4 px-6 text-slate-300">
                                    {{ $cat->equipments_count }} alat
                                </td>
                                <td class="py-4 px-6 text-right whitespace-nowrap space-x-2">
                                    <button
                                        type="button"
                                        wire:click="editCategory({{ $cat->id }})"
                                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#182330] hover:bg-rose-950/40 text-slate-300 hover:text-rose-300 border border-[#253648] hover:border-rose-800/40 transition cursor-pointer"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500">
                                    Belum ada kategori alat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[#182330] text-xs text-slate-400">
                {{ $categories->count() }} kategori terdaftar
            </div>
        </div>

    <!-- ================= FORM VIEW (Triggered by Tambah or Edit) ================= -->
    @else
        <!-- Form Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">
                    {{ $editingCategoryId ? 'Edit Kategori' : 'Tambah Kategori' }}
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
        <div class="bg-[#111720] border border-[#1A2533] rounded-2xl p-6 sm:p-8 space-y-6 max-w-2xl shadow-2xl">
            <form wire:submit="save" class="space-y-5">
                <!-- Nama Kategori -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-300">
                        Nama Kategori
                    </label>
                    <input
                        type="text"
                        wire:model="name"
                        placeholder="Contoh: Kamera Termal"
                        class="w-full bg-[#131B23] border {{ $errors->has('name') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-[#1D2A37]' }} rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#00FF66] transition"
                    >
                    @error('name')
                        <p class="text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-slate-300">Deskripsi</label>
                    <textarea
                        wire:model="description"
                        rows="3"
                        placeholder="Deskripsi singkat kategori (opsional)"
                        class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-[#00FF66] transition resize-none"
                    ></textarea>
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 rounded-xl font-bold text-xs bg-[#00FF66] text-black hover:bg-[#00E65C] disabled:opacity-80 disabled:cursor-wait transition shadow-[0_2px_10px_rgba(0,255,102,0.2)] cursor-pointer inline-flex items-center justify-center gap-2"
                    >
                        <span wire:loading.remove wire:target="save">Simpan Kategori</span>
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
    @if ($deletingCategoryId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="$set('deletingCategoryId', null)"></div>
            <div class="relative bg-[#111720] border border-[#1E2B38] rounded-2xl p-6 sm:p-7 max-w-sm w-full space-y-4 shadow-2xl z-10 text-center animate-in fade-in zoom-in-95 duration-200">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 mx-auto flex items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-white tracking-tight">Konfirmasi Hapus</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Hapus kategori <strong class="text-white">'{{ $deletingCategoryName }}'</strong>? Data kategori ini akan dilepas dari peralatan terkait.
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="$set('deletingCategoryId', null)"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="deleteCategory({{ $deletingCategoryId }})"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white transition shadow-lg shadow-rose-900/30 cursor-pointer"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
