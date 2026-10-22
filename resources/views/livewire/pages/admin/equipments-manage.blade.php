<?php

use App\Enums\DeviceType;
use App\Enums\UnitStatus;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Unit;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public bool $showForm = false;
    public ?int $selectedUnitId = null;
    public ?int $selectedEquipmentId = null;

    // Unit fields
    public string $unit_code = '';
    public string $status = 'tersedia';

    // Equipment fields
    public string $name = '';
    public string $device_type = 'thermal_camera';
    public array $category_ids = [];
    public float $rental_price_per_day = 850000;
    public string $completeness = 'Baterai, charger, hard case';

    // Dynamic custom specifications (saved as JSON into database column deskripsi)
    public array $customSpecs = [];

    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    public function addSpec(string $title = '', string $value = ''): void
    {
        $this->customSpecs[] = [
            'title' => $title,
            'value' => $value,
        ];
    }

    public function removeSpec(int $index): void
    {
        unset($this->customSpecs[$index]);
        $this->customSpecs = array_values($this->customSpecs);
    }

    public function mount(): void
    {
        $unit = Unit::query()->where('unit_code', 'GG-THM-002')->first() ?? Unit::query()->first();
        if ($unit) {
            $this->loadUnitData($unit->id);
        }
    }

    private function loadUnitData(int $unitId): void
    {
        $unit = Unit::query()->with('equipment.categories')->find($unitId);
        if (! $unit) return;

        $this->selectedUnitId = $unit->id;
        $this->unit_code = $unit->unit_code;
        $this->status = $unit->status->value;

        $eq = $unit->equipment;
        if ($eq) {
            $this->selectedEquipmentId = $eq->id;
            $this->name = $eq->name;
            $this->device_type = $eq->device_type->value;
            $this->rental_price_per_day = (float) $eq->rental_price_per_day;
            $this->category_ids = $eq->categories->pluck('id')->toArray();

            $specs = $eq->specifications;
            if (! empty($specs)) {
                $this->customSpecs = array_map(function ($s) {
                    return [
                        'title' => $s['label'] ?? '',
                        'value' => $s['value'] ?? '',
                    ];
                }, $specs);
            } else {
                $this->customSpecs = [
                    ['title' => 'Resolusi', 'value' => ''],
                    ['title' => 'Daya Tahan Baterai', 'value' => ''],
                    ['title' => 'Kelengkapan Unit', 'value' => 'Baterai, charger, hard case'],
                ];
            }
        }
    }

    public function selectUnit(int $unitId): void
    {
        $this->loadUnitData($unitId);
        $this->showForm = true;
        $this->resetValidation();
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function newUnit(): void
    {
        $this->selectedUnitId = null;
        $this->selectedEquipmentId = null;
        $this->unit_code = 'GG-NEW-' . rand(100, 999);
        $this->status = 'tersedia';
        $this->name = '';
        $this->device_type = 'thermal_camera';
        $this->category_ids = [];
        $this->rental_price_per_day = 500000;
        $this->completeness = 'Unit utama, kabel, baterai';
        $this->customSpecs = [
            ['title' => 'Resolusi', 'value' => '1920 × 1080 (Full HD)'],
            ['title' => 'Daya Tahan Baterai', 'value' => '6.0 jam'],
            ['title' => 'Kelengkapan Unit', 'value' => 'Unit utama, kabel, baterai'],
        ];
        $this->showForm = true;
        $this->resetValidation();
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $unitRules = [
            'unit_code' => ['required', 'string', 'max:50', Rule::unique('units', 'unit_code')->ignore($this->selectedUnitId)],
            'name' => ['required', 'string', 'max:255'],
            'device_type' => ['required', 'string'],
            'rental_price_per_day' => ['required', 'numeric', 'min:0'],
        ];

        $this->validate($unitRules, [
            'unit_code.required' => 'Kode unit wajib diisi dan harus unik.',
            'unit_code.unique' => 'Kode unit sudah digunakan, harap gunakan kode unik lain.',
            'name.required' => 'Nama alat wajib diisi.',
            'rental_price_per_day.required' => 'Tarif sewa wajib diisi.',
        ]);

        try {
            $cleanSpecs = [];
            foreach ($this->customSpecs as $item) {
                $t = trim($item['title'] ?? '');
                $v = trim($item['value'] ?? '');
                if ($t !== '' || $v !== '') {
                    $cleanSpecs[] = [
                        'title' => $t,
                        'value' => $v,
                    ];
                }
            }
            $deskripsiJson = json_encode($cleanSpecs, JSON_UNESCAPED_UNICODE);

            // 1. Update or create Equipment with deskripsi JSON column
            if ($this->selectedEquipmentId) {
                $equipment = Equipment::query()->find($this->selectedEquipmentId);
                $equipment->update([
                    'name' => $this->name,
                    'device_type' => $this->device_type,
                    'rental_price_per_day' => $this->rental_price_per_day,
                    'deskripsi' => $deskripsiJson,
                ]);
            } else {
                $equipment = Equipment::query()->create([
                    'name' => $this->name,
                    'device_type' => $this->device_type,
                    'rental_price_per_day' => $this->rental_price_per_day,
                    'deskripsi' => $deskripsiJson,
                ]);
                $this->selectedEquipmentId = $equipment->id;
            }

            if (! empty($this->category_ids)) {
                $equipment->categories()->sync($this->category_ids);
            }

            // 2. Update or create Unit
            if ($this->selectedUnitId) {
                $unit = Unit::query()->find($this->selectedUnitId);
                $unit->update([
                    'unit_code' => $this->unit_code,
                    'status' => $this->status,
                ]);
                $this->successMessage = "Unit {$unit->unit_code} berhasil diperbarui!";
            } else {
                $unit = Unit::query()->create([
                    'equipment_id' => $equipment->id,
                    'unit_code' => $this->unit_code,
                    'status' => $this->status,
                ]);
                $this->selectedUnitId = $unit->id;
                $this->successMessage = "Unit baru {$unit->unit_code} berhasil ditambahkan!";
            }

            $this->showForm = false;
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menyimpan perubahan: ' . $e->getMessage();
        }
    }

    public ?int $deletingUnitId = null;
    public ?string $deletingUnitCode = null;

    public function confirmDelete(int $id, string $code): void
    {
        $this->deletingUnitId = $id;
        $this->deletingUnitCode = $code;
    }

    public function cancelDelete(): void
    {
        $this->deletingUnitId = null;
        $this->deletingUnitCode = null;
    }

    public function deleteUnit(int $unitId): void
    {
        $unit = Unit::query()->find($unitId);
        if (! $unit) return;

        try {
            $code = $unit->unit_code;
            $unit->delete();
            $this->successMessage = "Unit {$code} berhasil dihapus.";
            $this->showForm = false;
            $this->cancelDelete();
        } catch (\DomainException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Tidak dapat menghapus: ' . $e->getMessage();
        }
    }

    public function with(): array
    {
        return [
            'unitsList' => Unit::query()->with('equipment.categories')->get(),
            'allCategories' => Category::query()->orderBy('name')->get(),
            'deviceTypes' => DeviceType::cases(),
        ];
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
                    Kelola Alat &amp; Unit
                </h1>
                <p class="text-sm text-slate-400">
                    Daftar seluruh unit fisik peralatan investigasi yang siap disewakan.
                </p>
            </div>

            <button
                type="button"
                wire:click="newUnit"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-[#00FF66] text-black hover:bg-[#00E65C] transition shadow-[0_2px_12px_rgba(0,255,102,0.25)] cursor-pointer self-start sm:self-auto"
            >
                <span class="text-sm font-bold">+</span>
                <span>Tambah Unit Baru</span>
            </button>
        </div>

        <!-- Table of All Units -->
        <div class="bg-[#111720] border border-[#1A2533] rounded-2xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#1A2533] text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-[#131B24]">
                            <th class="py-3.5 px-6">Kode Unit</th>
                            <th class="py-3.5 px-6">Nama Alat</th>
                            <th class="py-3.5 px-6">Kategori</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6">Tarif / Hari</th>
                            <th class="py-3.5 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#182330]">
                        @forelse ($unitsList as $u)
                            <tr class="hover:bg-[#141B24] transition">
                                <td class="py-3.5 px-6 font-mono font-bold text-white">{{ $u->unit_code }}</td>
                                <td class="py-3.5 px-6 font-medium text-slate-200">{{ $u->equipment?->name }}</td>
                                <td class="py-3.5 px-6 text-slate-400">
                                    {{ $u->equipment?->categories->pluck('name')->join(', ') ?: '-' }}
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase font-mono
                                        {{ $u->status === UnitStatus::Tersedia ? 'bg-emerald-950/70 text-emerald-400 border border-emerald-800/40' : 'bg-slate-800 text-slate-300' }}">
                                        {{ $u->status->value }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-slate-300">
                                    Rp{{ number_format($u->equipment?->rental_price_per_day ?: 0, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-6 text-right space-x-2 whitespace-nowrap">
                                    <button
                                        type="button"
                                        wire:click="selectUnit({{ $u->id }})"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $u->id }}, '{{ addslashes($u->unit_code) }}')"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#182330] hover:bg-rose-950/40 text-slate-300 hover:text-rose-300 border border-[#253648] hover:border-rose-800/40 transition cursor-pointer"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">Belum ada unit fisik terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[#182330] text-xs text-slate-400">
                {{ $unitsList->count() }} unit fisik terdaftar
            </div>
        </div>

    <!-- ================= FORM VIEW (Triggered by Tambah or Edit) ================= -->
    @else
        <!-- Form Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">
                    {{ $selectedUnitId ? 'Edit Unit ' . $unit_code : 'Tambah Unit Baru' }}
                </h1>
            </div>

            <button
                type="button"
                wire:click="cancel"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer self-start sm:self-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Daftar Unit</span>
            </button>
        </div>

        <!-- Form Cards -->
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Left Card: Informasi Unit -->
                <div class="lg:col-span-6 bg-[#111720] border border-[#1A2533] rounded-2xl p-6 sm:p-7 space-y-5 shadow-2xl">
                    <h2 class="text-base font-bold text-white">Informasi Unit</h2>

                    <!-- Kode Unit -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-300">Kode Unit</label>
                        <input
                            type="text"
                            wire:model="unit_code"
                            placeholder="Contoh: GG-THM-001"
                            class="w-full bg-[#131B23] border {{ $errors->has('unit_code') ? 'border-rose-500 ring-1 ring-rose-500' : 'border-[#1D2A37]' }} rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition font-mono"
                        >
                        @error('unit_code')
                            <p class="text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Alat -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-300">Nama Alat</label>
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="Nama alat"
                            class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition"
                        >
                        @error('name')
                            <p class="text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kategori & Jenis Perangkat -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Kategori Multiple -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-300">Kategori</label>
                            <div class="bg-[#131B23] border border-[#1D2A37] rounded-xl p-3 space-y-2">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($allCategories as $cat)
                                        <label class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-md cursor-pointer transition {{ in_array($cat->id, $category_ids) ? 'bg-[#182330] text-emerald-400 border border-emerald-500/30' : 'bg-[#0E141B] text-slate-400 border border-[#1E2B38]' }}">
                                            <input type="checkbox" wire:model="category_ids" value="{{ $cat->id }}" class="hidden">
                                            <span>{{ $cat->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Jenis Perangkat -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-300">Jenis Perangkat</label>
                            <select
                                wire:model.live="device_type"
                                class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition cursor-pointer"
                            >
                                <option value="thermal_camera">Kamera Termal</option>
                                <option value="night_vision_camera">Night Vision</option>
                                <option value="emf_meter">Detektor EMF</option>
                                <option value="evp_recorder">Perekam Audio</option>
                                <option value="motion_sensor">Sensor Gerak</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tarif Sewa / Hari & Status Unit -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-300">Tarif Sewa / Hari</label>
                            <input
                                type="number"
                                wire:model="rental_price_per_day"
                                class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-slate-300">Status Unit</label>
                            <select
                                wire:model="status"
                                class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition cursor-pointer"
                            >
                                <option value="tersedia">Tersedia</option>
                                <option value="ditahan">Ditahan</option>
                                <option value="dipinjam">Dipinjam</option>
                            </select>
                        </div>
                    </div>

                    <!-- Kelengkapan Unit -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-300">Kelengkapan Unit</label>
                        <input
                            type="text"
                            wire:model="completeness"
                            class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-[#00FF66] transition"
                        >
                    </div>
                </div>

                <!-- Right Card: Spesifikasi Kustom (Tersimpan dalam kolom deskripsi) -->
                <div class="lg:col-span-6 bg-[#111720] border border-[#1A2533] rounded-2xl p-6 sm:p-7 space-y-5 shadow-2xl flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold text-white flex items-center gap-2">
                                    <span>Spesifikasi Produk (Kustom)</span>
                                </h2>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Admin dapat menentukan judul dan isi spesifikasi bebas. Tersimpan ke kolom <span class="text-[#00FF66] font-mono">deskripsi</span>.
                                </p>
                            </div>
                            <button
                                type="button"
                                wire:click="addSpec"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#00FF66]/10 text-[#00FF66] border border-[#00FF66]/30 hover:bg-[#00FF66]/20 transition cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Tambah Spek</span>
                            </button>
                        </div>

                        <!-- Repeater rows -->
                        <div class="space-y-3 max-h-[460px] overflow-y-auto pr-1">
                            @forelse ($customSpecs as $index => $spec)
                                <div class="p-3 rounded-xl bg-[#0B0F15] border border-[#1E2C3D] flex items-center gap-3">
                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <div>
                                            <label class="block text-[10px] text-slate-400 mb-1 font-mono uppercase tracking-wider">Judul Spesifikasi</label>
                                            <input
                                                type="text"
                                                wire:model="customSpecs.{{ $index }}.title"
                                                placeholder="Contoh: Resolusi, Rentang Suhu"
                                                class="w-full bg-[#131B23] border border-[#1D2A37] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#00FF66] transition placeholder:text-slate-600"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-slate-400 mb-1 font-mono uppercase tracking-wider">Isi Spesifikasi</label>
                                            <input
                                                type="text"
                                                wire:model="customSpecs.{{ $index }}.value"
                                                placeholder="Contoh: 1920 × 1080 (Full HD)"
                                                class="w-full bg-[#131B23] border border-[#1D2A37] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#00FF66] transition placeholder:text-slate-600"
                                            >
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="removeSpec({{ $index }})"
                                        title="Hapus Spesifikasi"
                                        class="p-2 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/20 transition cursor-pointer shrink-0 mt-3 sm:mt-4"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            @empty
                                <div class="p-6 text-center rounded-xl bg-[#0B0F15] border border-dashed border-[#1E2C3D] text-slate-500 text-xs space-y-2">
                                    <p>Belum ada spesifikasi khusus untuk produk ini.</p>
                                    <button
                                        type="button"
                                        wire:click="addSpec('Resolusi', '')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#00FF66]/10 text-[#00FF66] border border-[#00FF66]/30 hover:bg-[#00FF66]/20 transition cursor-pointer"
                                    >
                                        + Tambah Baris Spesifikasi
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom Action Bar -->
            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="px-6 py-2.5 rounded-xl font-bold text-xs bg-[#00FF66] text-black hover:bg-[#00E65C] disabled:opacity-80 disabled:cursor-wait transition shadow-[0_2px_10px_rgba(0,255,102,0.2)] cursor-pointer inline-flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove wire:target="save">Simpan Perubahan Unit</span>
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
    @endif

    <!-- ================= CUSTOM MINIMALIST DELETE CONFIRMATION MODAL ================= -->
    @if ($deletingUnitId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="cancelDelete"></div>
            <div class="relative bg-[#111720] border border-[#1E2B38] rounded-2xl p-6 sm:p-7 max-w-sm w-full space-y-4 shadow-2xl z-10 text-center animate-in fade-in zoom-in-95 duration-200">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 mx-auto flex items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-white tracking-tight">Konfirmasi Hapus Unit</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Hapus unit fisik <strong class="text-white font-mono">'{{ $deletingUnitCode }}'</strong>? Unit ini tidak akan dapat dipinjam lagi.
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="deleteUnit({{ $deletingUnitId }})"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white transition shadow-lg shadow-rose-900/30 cursor-pointer"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
