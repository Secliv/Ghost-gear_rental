<?php

use App\Domain\Repositories\LoanRepositoryInterface;
use App\Enums\LoanStatus;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $filter = 'all';
    public string $timeFilter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function with(LoanRepositoryInterface $loanRepository): array
    {
        $userId = (int) auth()->id();
        $allLoans = $loanRepository->getMemberLoans($userId);

        $activeLoansCount = $allLoans->where('status', LoanStatus::Aktif)->count();
        $runningCount = $allLoans->filter(fn ($loan) => in_array($loan->status, [LoanStatus::Menunggu, LoanStatus::Aktif]))->count();
        $completedCount = $allLoans->filter(fn ($loan) => in_array($loan->status, [LoanStatus::Dikembalikan, LoanStatus::Ditolak]))->count();

        $loans = match ($this->filter) {
            'running' => $allLoans->filter(fn ($loan) => in_array($loan->status, [LoanStatus::Menunggu, LoanStatus::Aktif])),
            'completed' => $allLoans->filter(fn ($loan) => in_array($loan->status, [LoanStatus::Dikembalikan, LoanStatus::Ditolak])),
            default => $allLoans,
        };

        if ($this->timeFilter !== 'all') {
            $loans = $loans->filter(function ($loan) {
                $date = $loan->borrow_date ?? $loan->requested_at ?? $loan->created_at;
                if (! $date) {
                    return true;
                }
                $carbonDate = Carbon::parse($date);
                return match ($this->timeFilter) {
                    'today' => $carbonDate->isToday(),
                    'this_month' => $carbonDate->isCurrentMonth() && $carbonDate->isCurrentYear(),
                    'last_30_days' => $carbonDate->greaterThanOrEqualTo(now()->subDays(30)),
                    'this_year' => $carbonDate->isCurrentYear(),
                    default => true,
                };
            });
        }

        return [
            'loans' => $loans,
            'activeLoansCount' => $activeLoansCount,
            'runningCount' => $runningCount,
            'completedCount' => $completedCount,
            'totalCount' => $allLoans->count(),
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">
                Pinjaman Saya
            </h1>
            <p class="text-sm text-slate-400">
                Pantau pengajuan, tenggat pengembalian, dan biaya sewa Anda.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-medium">Per {{ now()->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    <!-- Flash Alert -->
    @if (session('success'))
        <div class="p-4 bg-[#00FF66]/10 border border-[#00FF66]/30 rounded-2xl flex items-center gap-3 text-sm text-[#00FF66] shadow-[0_4px_20px_rgba(0,255,102,0.1)]">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl flex items-center gap-3 text-sm text-rose-400 shadow-[0_4px_20px_rgba(244,63,94,0.1)]">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filter Bar: Status Tabs & Time Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1A2533] pb-3 text-xs">
        <!-- Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
            <button
                type="button"
                wire:click="setFilter('all')"
                class="px-3.5 py-1.5 rounded-lg font-medium transition cursor-pointer whitespace-nowrap {{ $filter === 'all' ? 'bg-[#18222D] text-[#00FF66] border border-[#00FF66]/30' : 'text-slate-400 hover:text-white hover:bg-[#141B24]' }}"
            >
                Semua ({{ $totalCount }})
            </button>
            <button
                type="button"
                wire:click="setFilter('running')"
                class="px-3.5 py-1.5 rounded-lg font-medium transition cursor-pointer whitespace-nowrap {{ $filter === 'running' ? 'bg-[#18222D] text-[#00FF66] border border-[#00FF66]/30' : 'text-slate-400 hover:text-white hover:bg-[#141B24]' }}"
            >
                Sedang Berjalan ({{ $runningCount }})
            </button>
            <button
                type="button"
                wire:click="setFilter('completed')"
                class="px-3.5 py-1.5 rounded-lg font-medium transition cursor-pointer whitespace-nowrap {{ $filter === 'completed' ? 'bg-[#18222D] text-[#00FF66] border border-[#00FF66]/30' : 'text-slate-400 hover:text-white hover:bg-[#141B24]' }}"
            >
                Selesai ({{ $completedCount }})
            </button>
        </div>

        <!-- Time Filter Dropdown -->
        <div class="flex items-center gap-2 shrink-0">
            <span class="text-slate-400 text-xs flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#00FF66]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Filter Waktu:</span>
            </span>
            <select
                wire:model.live="timeFilter"
                class="bg-[#111720] border border-[#1A2533] text-xs text-white rounded-lg px-3 py-1.5 focus:outline-none focus:border-[#00FF66] transition cursor-pointer"
            >
                <option value="all">Semua Waktu</option>
                <option value="today">Hari Ini</option>
                <option value="this_month">Bulan Ini</option>
                <option value="last_30_days">30 Hari Terakhir</option>
                <option value="this_year">Tahun Ini</option>
            </select>
        </div>
    </div>

    <!-- Single Unified Loans Table (Matching Mockup 5) -->
    <div class="bg-[#111720] border border-[#1A2533] rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#1A2533] text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-[#0E141C]">
                        <th class="py-3.5 px-6">Jenis Alat</th>
                        <th class="py-3.5 px-6">Periode Sewa</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6">Biaya Sewa</th>
                        <th class="py-3.5 px-6">Terlambat</th>
                        <th class="py-3.5 px-6">Denda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#182330]">
                    @forelse ($loans as $loan)
                        @php
                            $unit = $loan->unit;
                            $equipment = $unit?->equipment;
                            $borrowDateStr = $loan->borrow_date ? Carbon::parse($loan->borrow_date)->translatedFormat('d M Y') : ($loan->requested_at ? Carbon::parse($loan->requested_at)->translatedFormat('d M Y') : '-');
                            $dueDateStr = $loan->due_date ? Carbon::parse($loan->due_date)->translatedFormat('d M Y') : '-';
                            $returnDateStr = $loan->return_date ? Carbon::parse($loan->return_date)->translatedFormat('d M Y') : null;
                            $isLate = $loan->isLate();
                            $lateDays = $isLate && $loan->due_date ? max(0, Carbon::now()->startOfDay()->diffInDays(Carbon::parse($loan->due_date)->startOfDay())) : 0;
                        @endphp
                        <tr class="hover:bg-[#141B24] transition">
                            <!-- Jenis Alat -->
                            <td class="py-4 px-6 font-semibold text-white">
                                <div class="text-sm font-bold text-white">{{ $equipment?->name ?? 'Peralatan' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $unit?->unit_code }}</div>
                            </td>

                            <!-- Periode Sewa -->
                            <td class="py-4 px-6 text-slate-300">
                                <div class="font-medium text-slate-200">{{ $borrowDateStr }} &ndash; {{ $dueDateStr }}</div>
                                @if ($loan->status === LoanStatus::Aktif)
                                    <div class="text-[11px] text-slate-400 mt-0.5">Tenggat: {{ $dueDateStr }}</div>
                                @elseif ($loan->status === LoanStatus::Menunggu)
                                    <div class="text-[11px] text-slate-400 mt-0.5">Diajukan {{ $loan->requested_at?->translatedFormat('d M Y') }}</div>
                                @elseif ($loan->status === LoanStatus::Dikembalikan && $returnDateStr)
                                    <div class="text-[11px] text-slate-400 mt-0.5">Kembali: {{ $returnDateStr }}</div>
                                @elseif ($loan->status === LoanStatus::Ditolak)
                                    <div class="text-[11px] text-rose-400/80 mt-0.5">Pengajuan ditolak</div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-6">
                                @if ($loan->status === LoanStatus::Aktif)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-950/70 text-emerald-400 border border-emerald-800/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#00FF66]"></span>
                                        Dipinjam
                                    </span>
                                @elseif ($loan->status === LoanStatus::Menunggu)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#182330] text-slate-300 border border-[#25364A]">
                                        Menunggu
                                    </span>
                                @elseif ($loan->status === LoanStatus::Dikembalikan)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-800/80 text-slate-300 border border-slate-700/60">
                                        Dikembalikan
                                    </span>
                                @elseif ($loan->status === LoanStatus::Ditolak)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-rose-950/70 text-rose-300 border border-rose-800/50">
                                        Ditolak
                                    </span>
                                @endif
                            </td>

                            <!-- Biaya Sewa -->
                            <td class="py-4 px-6 font-medium text-white">
                                <div class="text-sm font-bold text-white">Rp{{ number_format($loan->rental_fee ?: ($equipment?->rental_price_per_day && $loan->borrow_date && $loan->due_date ? $equipment->rental_price_per_day * max(1, Carbon::parse($loan->borrow_date)->diffInDays(Carbon::parse($loan->due_date)) + 1) : 0), 0, ',', '.') }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Tarif sewa alat</div>
                            </td>

                            <!-- Terlambat -->
                            <td class="py-4 px-6">
                                @if ($loan->status === LoanStatus::Aktif && $isLate && $lateDays > 0)
                                    <span class="text-rose-400 font-bold">{{ $lateDays }} hari</span>
                                @elseif ($loan->status === LoanStatus::Aktif)
                                    <span class="text-slate-300">0 hari</span>
                                @elseif ($loan->status === LoanStatus::Dikembalikan && $loan->late_days > 0)
                                    <span class="text-rose-400 font-bold">{{ $loan->late_days }} hari</span>
                                @elseif ($loan->status === LoanStatus::Dikembalikan)
                                    <span class="text-slate-300">0 hari</span>
                                @else
                                    <span class="text-slate-500">&mdash;</span>
                                @endif
                            </td>

                            <!-- Denda -->
                            <td class="py-4 px-6">
                                @if ($loan->status === LoanStatus::Aktif && $isLate && $lateDays > 0)
                                    <div class="text-rose-400 font-bold">Rp{{ number_format($lateDays * 100000, 0, ',', '.') }}</div>
                                    <div class="text-[10px] text-rose-300">Belum dibayar</div>
                                @elseif ($loan->status === LoanStatus::Aktif)
                                    <div class="text-slate-300 font-medium">Rp0</div>
                                    <div class="text-[10px] text-slate-400">Belum ada denda</div>
                                @elseif ($loan->status === LoanStatus::Dikembalikan && $loan->fine_amount > 0)
                                    <div class="text-slate-200 font-bold">Rp{{ number_format($loan->fine_amount, 0, ',', '.') }}</div>
                                    <div class="text-[10px] {{ $loan->fine_paid ? 'text-[#00FF66]' : 'text-rose-400' }}">
                                        {{ $loan->fine_paid ? 'Lunas' : 'Belum lunas' }}
                                    </div>
                                @elseif ($loan->status === LoanStatus::Dikembalikan)
                                    <div class="text-slate-300 font-medium">Rp0</div>
                                    <div class="text-[10px] text-[#00FF66]">Lunas</div>
                                @else
                                    <div class="text-slate-500">&mdash;</div>
                                    <div class="text-[10px] text-slate-500">Belum berjalan</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                Tidak ada data pinjaman yang ditemukan pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer Matching Mockup 5 -->
        <div class="p-4 sm:p-5 border-t border-[#182330] flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-400 gap-3 bg-[#0D131B]">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-[#00FF66]"></span>
                <span><strong class="text-white">{{ $activeLoansCount }}</strong> unit aktif dari maksimal <strong>2</strong> unit. Pengajuan belum dihitung sebagai pinjaman aktif.</span>
            </div>
            <div class="text-slate-500 text-[11px]">
                Maks. 5 hari / pinjaman &bull; Denda dihitung setelah tenggat pengembalian.
            </div>
        </div>
    </div>
</div>
