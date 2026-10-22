<?php

use App\Models\Category;
use App\Models\Equipment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public ?int $selectedCategory = null;

    public function with(App\Application\Services\EquipmentCatalogService $catalogService): array
    {
        $equipments = $catalogService->getCatalogEquipments(
            search: $this->search !== '' ? $this->search : null,
            categoryId: $this->selectedCategory,
        );

        $categories = Category::query()->orderBy('name')->get();

        return [
            'equipments' => $equipments,
            'categories' => $categories,
        ];
    }
}; ?>

<div class="space-y-8">
    <!-- Header Title & Subtitle -->
    <div>
        <h1 class="text-3xl font-extrabold tracking-tight text-white mb-2">
            Katalog Unit
        </h1>
        <p class="text-sm text-slate-400">
            Pilih jenis alat untuk kebutuhan investigasi Anda. Maksimal 2 unit aktif per anggota.
        </p>
    </div>

    <!-- Search and Filter Bar -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Search Input with icon -->
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="Cari nama atau kategori alat..."
                    class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl pl-10 pr-10 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-[#00FF66] focus:ring-1 focus:ring-[#00FF66] transition"
                >
                <div wire:loading wire:target="search" class="absolute right-3.5 top-3 text-[#00FF66]">
                    <svg class="animate-spin h-4 w-4 text-[#00FF66]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            <!-- Available Count -->
            <div class="text-xs text-slate-400 font-medium">
                {{ $equipments->count() }} jenis alat tersedia
            </div>
        </div>

        <!-- Category Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <button
                type="button"
                wire:click="$set('selectedCategory', null)"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $selectedCategory === null ? 'bg-[#00FF66] text-black shadow-[0_2px_10px_rgba(0,255,102,0.25)]' : 'bg-[#111720] text-slate-300 hover:text-white hover:bg-[#182330] border border-[#1A2533]' }}"
            >
                Semua Kategori
            </button>
            @foreach ($categories as $cat)
                <button
                    type="button"
                    wire:click="$set('selectedCategory', {{ $cat->id }})"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer {{ $selectedCategory === $cat->id ? 'bg-[#00FF66] text-black shadow-[0_2px_10px_rgba(0,255,102,0.25)]' : 'bg-[#111720] text-slate-300 hover:text-white hover:bg-[#182330] border border-[#1A2533]' }}"
                >
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Equipment Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($equipments as $equipment)
            @php
                $availableUnits = $equipment->available_units_count ?? $equipment->availableUnits()->count();
                $typeLabel = match($equipment->device_type?->value ?? '') {
                    'thermal_camera' => 'Thermal',
                    'night_vision_camera' => 'Night Vision',
                    'emf_meter' => 'EMF',
                    'evp_recorder' => 'EVP',
                    'motion_sensor' => 'Sensor Gerak',
                    default => 'Peralatan',
                };
                $imagePath = $equipment->image ? asset($equipment->image) : asset('images/kamera-termal.png');
            @endphp
            <a
                href="{{ route('equipment.detail', $equipment) }}"
                wire:navigate
                class="bg-[#111720] border border-[#1A2533] rounded-2xl overflow-hidden p-6 flex flex-col justify-between hover:border-[#00FF66]/50 hover:bg-[#131B26] transition duration-200 group cursor-pointer block"
            >
                <!-- Top Image Area -->
                <div class="h-48 sm:h-52 w-full flex items-center justify-center p-3 relative mb-4">
                    <img src="{{ $imagePath }}" alt="{{ $equipment->name }}" class="max-h-full max-w-full object-contain transition duration-300 group-hover:scale-105 drop-shadow-[0_10px_20px_rgba(0,0,0,0.5)]">
                </div>

                <!-- Info Section -->
                <div class="space-y-4">
                    <!-- Device Type & Availability Pill -->
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium font-mono text-[11px]">{{ $typeLabel }}</span>
                        @if ($availableUnits > 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-950/70 text-emerald-400 border border-emerald-800/50">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Tersedia ({{ $availableUnits }} Unit)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-800/70 text-slate-400 border border-slate-700/50">
                                Unit Habis
                            </span>
                        @endif
                    </div>

                    <!-- Category Pills -->
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($equipment->categories as $category)
                            <span class="px-2.5 py-0.5 text-xs rounded-md bg-[#16212E] text-slate-300 border border-[#243547]">
                                {{ $category->name }}
                            </span>
                        @endforeach
                    </div>

                    <!-- Name -->
                    <h3 class="text-base font-bold text-white group-hover:text-[#00FF66] transition line-clamp-1">
                        {{ $equipment->name }}
                    </h3>

                    <!-- Price & Action Button -->
                    <div class="flex items-center justify-between pt-2 border-t border-[#182330]">
                        <div>
                            <div class="text-base font-bold text-white">
                                Rp{{ number_format($equipment->rental_price_per_day, 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-slate-400">/ hari</div>
                        </div>

                        <span class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-bold bg-[#00FF66] text-black group-hover:bg-[#00E65C] group-hover:shadow-[0_2px_12px_rgba(0,255,102,0.3)] transition shadow-[0_2px_10px_rgba(0,255,102,0.2)]">
                            Detail & Pinjam
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-16 text-center bg-[#111720] border border-[#1A2533] rounded-2xl">
                <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-slate-300 font-medium">Tidak ada peralatan yang sesuai dengan pencarian.</p>
                <p class="text-xs text-slate-500 mt-1">Coba kata kunci lain atau kosongkan filter pencarian.</p>
            </div>
        @endforelse
    </div>
</div>
