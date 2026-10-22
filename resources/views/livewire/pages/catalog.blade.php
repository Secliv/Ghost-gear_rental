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

<div class="py-8 bg-slate-950 min-h-screen text-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <!-- Hero & Search Section -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none text-9xl">
                👻
            </div>

            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Armada Peralatan Siap Operasi
                </span>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white mb-2">
                    Katalog Perlengkapan Investigasi
                </h1>
                <p class="text-slate-400 text-sm sm:text-base mb-6">
                    Sewa instrumen paranormal kelas laboratorium untuk ekspedisi Anda. Seluruh unit telah dikalibrasi berkala.
                </p>

                <!-- Search and Filters -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 relative">
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Cari nama alat, fungsi, atau kata kunci..."
                               class="w-full bg-slate-950/80 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm shadow-inner">
                        <div wire:loading wire:target="search" class="absolute right-3 top-3.5 text-xs text-emerald-400">
                            Mencari...
                        </div>
                    </div>

                    <div>
                        <select wire:model.live="selectedCategory"
                                class="w-full bg-slate-950/80 border border-slate-700 rounded-xl px-3 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-inner">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipments Grid -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-200">
                    Daftar Peralatan ({{ count($equipments) }})
                </h2>
                @if ($search || $selectedCategory)
                    <button wire:click="$set('search', ''); $set('selectedCategory', null)" class="text-xs text-emerald-400 hover:underline">
                        Reset Filter
                    </button>
                @endif
            </div>

            @if ($equipments->isEmpty())
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center">
                    <div class="text-5xl mb-3">🔍</div>
                    <h3 class="text-lg font-semibold text-white mb-1">Peralatan Tidak Ditemukan</h3>
                    <p class="text-sm text-slate-400">Tidak ada alat investigasi yang cocok dengan pencarian Anda.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($equipments as $item)
                        <div class="bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-2xl p-5 flex flex-col justify-between transition-all duration-200 hover:-translate-y-1 shadow-lg hover:shadow-emerald-950/20 group">
                            <div>
                                <!-- Top Badges -->
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-800 text-emerald-400 border border-slate-700">
                                        {{ $item->device_type->label() }}
                                    </span>

                                    @if ($item->available_units_count > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            {{ $item->available_units_count }} Unit Tersedia
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                            Habis Dipinjam
                                        </span>
                                    @endif
                                </div>

                                <!-- Title -->
                                <h3 class="text-lg font-bold text-white group-hover:text-emerald-300 transition mb-2">
                                    {{ $item->name }}
                                </h3>

                                <!-- Categories -->
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    @foreach ($item->categories as $category)
                                        <span class="text-[11px] px-2 py-0.5 bg-slate-800/80 text-slate-300 rounded border border-slate-700/60">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>

                                <!-- Description -->
                                <p class="text-xs text-slate-400 line-clamp-3 mb-4 leading-relaxed">
                                    {{ $item->description }}
                                </p>
                            </div>

                            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between">
                                <div class="text-xs text-slate-500">
                                    Total: {{ $item->units_count }} unit
                                </div>
                                <a href="{{ route('equipment.detail', $item->id) }}"
                                   wire:navigate
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs transition shadow">
                                    <span>Detail & Pinjam</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
