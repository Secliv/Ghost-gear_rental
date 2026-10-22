<?php

use App\Enums\UnitStatus;
use App\Models\Equipment;
use App\Models\Unit;
use App\Services\LoanService;
use DomainException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Equipment $equipment;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    public function mount(Equipment $equipment): void
    {
        $this->equipment = $equipment->load(['categories', 'units']);
    }

    public function requestLoan(int $unitId, LoanService $loanService): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $unit = Unit::query()->find($unitId);

        if (! $unit) {
            $this->errorMessage = 'Unit tidak ditemukan.';
            return;
        }

        try {
            $loan = $loanService->request(auth()->user(), $unit);
            $this->successMessage = "Pengajuan peminjaman untuk unit {$unit->unit_code} berhasil dibuat! Status pengajuan saat ini: Menunggu persetujuan admin.";
            $this->equipment->load('units');
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Terjadi kesalahan sistem saat mengajukan peminjaman: ' . $e->getMessage();
        }
    }
}; ?>

<div class="py-8 bg-slate-950 min-h-screen text-slate-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Back Link -->
        <div>
            <a href="{{ route('catalog') }}" wire:navigate class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-emerald-400 transition">
                <span>&larr;</span>
                <span>Kembali ke Katalog</span>
            </a>
        </div>

        <!-- Alert Messages -->
        @if ($successMessage)
            <div class="bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 px-5 py-4 rounded-xl flex items-start gap-3 shadow-lg">
                <span class="text-xl">✅</span>
                <div class="flex-1 text-sm font-medium">
                    {{ $successMessage }}
                    <div class="mt-2">
                        <a href="{{ route('member.loans') }}" wire:navigate class="underline text-emerald-300 hover:text-white font-semibold">
                            Lihat status pengajuan di menu Pinjaman Saya &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if ($errorMessage)
            <div class="bg-rose-950/80 border border-rose-500/40 text-rose-200 px-5 py-4 rounded-xl flex items-start gap-3 shadow-lg">
                <span class="text-xl">⚠️</span>
                <div class="flex-1 text-sm font-medium">
                    {{ $errorMessage }}
                </div>
            </div>
        @endif

        <!-- Main Card Header -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    {{ $equipment->device_type->label() }}
                </span>
                @foreach ($equipment->categories as $category)
                    <span class="px-2.5 py-0.5 rounded text-xs bg-slate-800 text-slate-300 border border-slate-700">
                        {{ $category->name }}
                    </span>
                @endforeach
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-white mb-4">
                {{ $equipment->name }}
            </h1>

            <p class="text-slate-300 text-sm leading-relaxed mb-6">
                {{ $equipment->description }}
            </p>

            <!-- Technical Specifications (Only filled ones!) -->
            <div class="pt-6 border-t border-slate-800">
                <h3 class="text-sm font-bold tracking-wider text-slate-400 uppercase mb-4 flex items-center gap-2">
                    <span>⚙️</span> Spesifikasi Teknis Terkalibrasi
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @if ($equipment->emf_range)
                        <div class="bg-slate-950/60 border border-slate-800/80 p-3.5 rounded-xl">
                            <div class="text-xs text-slate-500 mb-1">Rentang EMF</div>
                            <div class="text-sm font-semibold text-emerald-300">{{ $equipment->emf_range }}</div>
                        </div>
                    @endif

                    @if ($equipment->thermal_temp_range_c)
                        <div class="bg-slate-950/60 border border-slate-800/80 p-3.5 rounded-xl">
                            <div class="text-xs text-slate-500 mb-1">Rentang Suhu Termal</div>
                            <div class="text-sm font-semibold text-amber-300">{{ $equipment->thermal_temp_range_c }}</div>
                        </div>
                    @endif

                    @if ($equipment->night_vision_resolution)
                        <div class="bg-slate-950/60 border border-slate-800/80 p-3.5 rounded-xl">
                            <div class="text-xs text-slate-500 mb-1">Resolusi Night Vision</div>
                            <div class="text-sm font-semibold text-cyan-300">{{ $equipment->night_vision_resolution }}</div>
                        </div>
                    @endif

                    @if ($equipment->audio_record_hours)
                        <div class="bg-slate-950/60 border border-slate-800/80 p-3.5 rounded-xl">
                            <div class="text-xs text-slate-500 mb-1">Durasi Rekam Audio</div>
                            <div class="text-sm font-semibold text-purple-300">{{ $equipment->audio_record_hours }} Jam</div>
                        </div>
                    @endif

                    @if ($equipment->motion_sensor_type)
                        <div class="bg-slate-950/60 border border-slate-800/80 p-3.5 rounded-xl">
                            <div class="text-xs text-slate-500 mb-1">Tipe Sensor Gerak</div>
                            <div class="text-sm font-semibold text-indigo-300">{{ $equipment->motion_sensor_type }}</div>
                        </div>
                    @endif

                    @if ($equipment->battery_hours)
                        <div class="bg-slate-950/60 border border-slate-800/80 p-3.5 rounded-xl">
                            <div class="text-xs text-slate-500 mb-1">Daya Tahan Baterai</div>
                            <div class="text-sm font-semibold text-sky-300">{{ $equipment->battery_hours }} Jam</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Physical Units List -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-white">Daftar Unit Fisik</h2>
                    <p class="text-xs text-slate-400">Pilih salah satu unit yang tersedia untuk mengajukan pinjaman.</p>
                </div>
                <div class="text-xs text-slate-400">
                    Maksimal 2 pinjaman aktif per anggota.
                </div>
            </div>

            <div class="divide-y divide-slate-800">
                @forelse ($equipment->units as $unit)
                    <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-mono font-bold text-xs text-slate-200">
                                #{{ $loop->iteration }}
                            </div>
                            <div>
                                <div class="font-mono font-bold text-white text-base">
                                    {{ $unit->unit_code }}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if ($unit->status === UnitStatus::Tersedia)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Tersedia untuk Dipinjam
                                        </span>
                                    @elseif ($unit->status === UnitStatus::Ditahan)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            Ditahan (Menunggu Persetujuan)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                            Sedang Dipinjam
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            @if ($unit->status === UnitStatus::Tersedia)
                                <button wire:click="requestLoan({{ $unit->id }})"
                                        wire:loading.attr="disabled"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-semibold text-xs transition shadow-md hover:shadow-emerald-900/30">
                                    <span wire:loading.remove wire:target="requestLoan({{ $unit->id }})">🚀 Ajukan Pinjaman</span>
                                    <span wire:loading wire:target="requestLoan({{ $unit->id }})">Memproses...</span>
                                </button>
                            @else
                                <button disabled
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-xl bg-slate-800 text-slate-500 font-semibold text-xs cursor-not-allowed border border-slate-700/60">
                                    Tidak Tersedia
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-sm text-slate-500">
                        Belum ada unit fisik yang didaftarkan untuk alat ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
