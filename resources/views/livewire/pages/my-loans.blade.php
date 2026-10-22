<?php

use App\Domain\Repositories\LoanRepositoryInterface;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(LoanRepositoryInterface $loanRepository): array
    {
        $userId = (int) auth()->id();
        $loans = $loanRepository->getMemberLoans($userId);

        $activeCount = $loans->where('status', LoanStatus::Aktif)->count();
        $pendingCount = $loans->where('status', LoanStatus::Menunggu)->count();
        $unpaidFines = $loans->where('fine_paid', false)->sum('fine_amount');

        return [
            'loans' => $loans,
            'activeCount' => $activeCount,
            'pendingCount' => $pendingCount,
            'unpaidFines' => $unpaidFines,
        ];
    }
}; ?>

<div class="py-8 bg-slate-950 min-h-screen text-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">
                    Pinjaman Saya
                </h1>
                <p class="text-sm text-slate-400 mt-1">
                    Daftar permohonan dan riwayat peminjaman instrumen paranormal Anda.
                </p>
            </div>
            <div>
                <a href="{{ route('catalog') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs transition shadow">
                    <span>+ Ajukan Alat Baru</span>
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="text-xs text-slate-400 font-medium">Pinjaman Aktif Sedang Berjalan</div>
                <div class="text-2xl font-black text-white mt-1">{{ $activeCount }} / {{ config('ghostgear.max_active_loans', 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Maksimal 2 alat aktif sekaligus</div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="text-xs text-slate-400 font-medium">Pengajuan Menunggu Persetujuan</div>
                <div class="text-2xl font-black text-amber-400 mt-1">{{ $pendingCount }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Menunggu otorisasi admin</div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <div class="text-xs text-slate-400 font-medium">Total Denda Belum Lunas</div>
                <div class="text-2xl font-black text-rose-400 mt-1">Rp {{ number_format($unpaidFines, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Dibayarkan di luar sistem kepada admin</div>
            </div>
        </div>

        <!-- Loans List -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
            <h2 class="text-lg font-bold text-white mb-6">
                Riwayat & Status Peminjaman
            </h2>

            @if ($loans->isEmpty())
                <div class="text-center py-12">
                    <div class="text-5xl mb-3">📦</div>
                    <h3 class="text-base font-semibold text-white">Belum Ada Peminjaman</h3>
                    <p class="text-xs text-slate-400 mt-1 mb-4">Anda belum mengajukan peminjaman alat investigasi apapun.</p>
                    <a href="{{ route('catalog') }}" wire:navigate class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold">
                        Jelajahi Katalog Alat
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($loans as $loan)
                        <div class="bg-slate-950/70 border {{ $loan->isLate() ? 'border-rose-500/50' : 'border-slate-800' }} rounded-xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 transition hover:border-slate-700">
                            <!-- Left Info -->
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-800 text-emerald-400 font-bold border border-slate-700">
                                        {{ $loan->unit?->unit_code }}
                                    </span>

                                    <!-- Status Badge -->
                                    @if ($loan->status === LoanStatus::Menunggu)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            Menunggu Persetujuan
                                        </span>
                                    @elseif ($loan->status === LoanStatus::Aktif)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/20">
                                            Sedang Dipinjam
                                        </span>
                                    @elseif ($loan->status === LoanStatus::Ditolak)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                            Pengajuan Ditolak
                                        </span>
                                    @elseif ($loan->status === LoanStatus::Dikembalikan)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            Telah Dikembalikan
                                        </span>
                                    @endif

                                    <!-- Late Indicator -->
                                    @if ($loan->isLate())
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40 animate-pulse">
                                            ⚠️ TERLAMBAT
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-base font-bold text-white">
                                    {{ $loan->unit?->equipment?->name }}
                                </h3>

                                <div class="text-xs text-slate-400">
                                    Diajukan pada: {{ $loan->requested_at->format('d M Y, H:i') }} WIB
                                </div>
                            </div>

                            <!-- Right Details -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-3 md:pt-0 border-t md:border-t-0 border-slate-800 text-xs">
                                <div>
                                    <div class="text-slate-500">Mulai Pinjam</div>
                                    <div class="font-medium text-slate-200 mt-0.5">
                                        {{ $loan->borrow_date ? $loan->borrow_date->format('d M Y') : '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-slate-500">Tenggat Waktu</div>
                                    <div class="font-semibold {{ $loan->isLate() ? 'text-rose-400' : 'text-slate-200' }} mt-0.5">
                                        {{ $loan->due_date ? $loan->due_date->format('d M Y') : '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-slate-500">Tgl Dikembalikan</div>
                                    <div class="font-medium text-slate-200 mt-0.5">
                                        {{ $loan->return_date ? $loan->return_date->format('d M Y') : '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-slate-500">Denda Keterlambatan</div>
                                    <div class="font-semibold {{ $loan->fine_amount > 0 ? 'text-rose-400' : 'text-slate-400' }} mt-0.5">
                                        @if ($loan->fine_amount > 0)
                                            Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}
                                            <span class="text-[10px] block {{ $loan->fine_paid ? 'text-emerald-400' : 'text-rose-300' }}">
                                                ({{ $loan->fine_paid ? 'Lunas' : 'Belum Lunas' }})
                                            </span>
                                        @else
                                            Rp 0
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
