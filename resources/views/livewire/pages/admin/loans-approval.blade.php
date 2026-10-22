<?php

use App\Application\Services\LoanService;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    public function with(): array
    {
        $pendingLoans = Loan::query()
            ->with(['member.profile', 'unit.equipment'])
            ->where('status', LoanStatus::Menunggu)
            ->latest('requested_at')
            ->get();

        $activeLoans = Loan::query()
            ->with(['member.profile', 'unit.equipment'])
            ->whereIn('status', [LoanStatus::Aktif, LoanStatus::Dikembalikan])
            ->where(function ($q) {
                $q->where('status', LoanStatus::Aktif)
                    ->orWhere(function ($sub) {
                        $sub->where('status', LoanStatus::Dikembalikan)
                            ->where('fine_paid', false)
                            ->where('fine_amount', '>', 0);
                    });
            })
            ->latest('borrow_date')
            ->get();

        return [
            'pendingLoans' => $pendingLoans,
            'activeLoans' => $activeLoans,
        ];
    }

    public function approve(int $loanId, LoanService $loanService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $loan = Loan::query()->find($loanId);
        if (! $loan) {
            $this->errorMessage = 'Data pinjaman tidak ditemukan.';
            return;
        }

        try {
            $loanService->approve($loan, auth()->user());
            $this->successMessage = "Pengajuan pinjaman untuk unit {$loan->unit->unit_code} berhasil disetujui.";
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menyetujui: ' . $e->getMessage();
        }
    }

    public ?int $rejectingLoanId = null;
    public ?string $rejectingUnitCode = null;
    public ?string $rejectingMemberName = null;

    public function confirmReject(int $id, string $unitCode, string $memberName): void
    {
        $this->rejectingLoanId = $id;
        $this->rejectingUnitCode = $unitCode;
        $this->rejectingMemberName = $memberName;
    }

    public function cancelReject(): void
    {
        $this->rejectingLoanId = null;
        $this->rejectingUnitCode = null;
        $this->rejectingMemberName = null;
    }

    public function reject(int $loanId, LoanService $loanService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $loan = Loan::query()->find($loanId);
        if (! $loan) {
            $this->errorMessage = 'Data pinjaman tidak ditemukan.';
            $this->cancelReject();
            return;
        }

        try {
            $loanService->reject($loan, auth()->user());
            $this->successMessage = "Pengajuan pinjaman untuk unit {$loan->unit->unit_code} berhasil ditolak.";
            $this->cancelReject();
        } catch (\DomainException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menolak: ' . $e->getMessage();
        }
    }

    public function processReturn(int $loanId, LoanService $loanService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $loan = Loan::query()->find($loanId);
        if (! $loan) {
            $this->errorMessage = 'Data pinjaman tidak ditemukan.';
            return;
        }

        try {
            $loanService->returnLoan($loan, auth()->user());
            $this->successMessage = "Pengembalian unit {$loan->unit->unit_code} berhasil dicatat! Status unit kini Tersedia.";
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal mencatat pengembalian: ' . $e->getMessage();
        }
    }

    public function markPaid(int $loanId, LoanService $loanService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $loan = Loan::query()->find($loanId);
        if (! $loan) {
            $this->errorMessage = 'Data pinjaman tidak ditemukan.';
            return;
        }

        try {
            $loanService->markFinePaid($loan, auth()->user());
            $this->successMessage = "Denda untuk peminjaman unit {$loan->unit->unit_code} berhasil ditandai Lunas!";
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal mengubah status denda: ' . $e->getMessage();
        }
    }
}; ?>

<div class="space-y-10">

    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">
            Persetujuan &amp; Pengembalian
        </h1>
        <p class="text-sm text-slate-400">
            Tinjau pengajuan anggota dan selesaikan pengembalian unit.
        </p>
    </div>

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

    <!-- Section 1: Menunggu Persetujuan -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-white">Menunggu persetujuan</h2>
            <span class="text-xs text-slate-400 font-medium">{{ $pendingLoans->count() }} pengajuan</span>
        </div>

        <div class="bg-[#111720] border border-[#1A2533] rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#1A2533] text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3.5 px-6">Anggota</th>
                            <th class="py-3.5 px-6">Unit / Alat</th>
                            <th class="py-3.5 px-6">Periode</th>
                            <th class="py-3.5 px-6">Unit Aktif</th>
                            <th class="py-3.5 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#182330]">
                        @forelse ($pendingLoans as $loan)
                            @php
                                $member = $loan->member;
                                $unit = $loan->unit;
                                $equipment = $unit?->equipment;
                                $memberActiveLoans = \App\Models\Loan::where('member_id', $member->id)
                                    ->where('status', LoanStatus::Aktif)
                                    ->count();
                                $borrowDateStr = $loan->borrow_date ? Carbon::parse($loan->borrow_date)->translatedFormat('d') : ($loan->requested_at ? Carbon::parse($loan->requested_at)->translatedFormat('d') : '');
                                $dueDateStr = $loan->due_date ? Carbon::parse($loan->due_date)->translatedFormat('d M Y') : '';
                            @endphp
                            <tr class="hover:bg-[#141B24] transition">
                                <!-- Anggota -->
                                <td class="py-4 px-6 font-semibold text-white">
                                    <div>{{ $member->profile?->full_name ?? $member->username }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $member->profile?->member_code ?? 'AGT-' . $member->id }}</div>
                                </td>

                                <!-- Unit / Alat -->
                                <td class="py-4 px-6 text-slate-300">
                                    <div class="font-medium text-white">{{ $equipment?->name ?? 'Peralatan' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $unit?->unit_code }}</div>
                                </td>

                                <!-- Periode -->
                                <td class="py-4 px-6 text-slate-300">
                                    <div>{{ $borrowDateStr }} &ndash; {{ $dueDateStr }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">Rp{{ number_format($loan->rental_fee ?: ($equipment?->rental_price_per_day && $loan->borrow_date && $loan->due_date ? $equipment->rental_price_per_day * max(1, Carbon::parse($loan->borrow_date)->diffInDays(Carbon::parse($loan->due_date)) + 1) : 0), 0, ',', '.') }}</div>
                                </td>

                                <!-- Unit Aktif -->
                                <td class="py-4 px-6 text-slate-300 font-medium">
                                    {{ $memberActiveLoans }} / 2 unit
                                </td>

                                <!-- Tindakan -->
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="approve({{ $loan->id }})"
                                            wire:loading.attr="disabled"
                                            class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-[#00FF66] text-black hover:bg-[#00E65C] disabled:opacity-50 disabled:cursor-wait transition cursor-pointer"
                                        >
                                            Setujui
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="confirmReject({{ $loan->id }}, '{{ addslashes($unit?->unit_code ?? '') }}', '{{ addslashes($member->profile?->full_name ?? $member->username) }}')"
                                            wire:loading.attr="disabled"
                                            class="px-3.5 py-1.5 rounded-xl font-semibold text-xs bg-[#182330] hover:bg-rose-950/40 text-slate-300 hover:text-rose-300 border border-[#253648] hover:border-rose-800/40 disabled:opacity-50 disabled:cursor-wait transition cursor-pointer"
                                        >
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">
                                    Tidak ada pengajuan pinjaman yang menunggu persetujuan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section 2: Pengembalian Unit -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-white">Pengembalian unit</h2>
            <span class="text-xs text-slate-400 font-medium">Per {{ now()->translatedFormat('d F Y') }}</span>
        </div>

        <div class="bg-[#111720] border border-[#1A2533] rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#1A2533] text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3.5 px-6">Anggota / Unit</th>
                            <th class="py-3.5 px-6">Tenggat</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6">Keterlambatan / Denda</th>
                            <th class="py-3.5 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#182330]">
                        @forelse ($activeLoans as $loan)
                            @php
                                $member = $loan->member;
                                $unit = $loan->unit;
                                $equipment = $unit?->equipment;
                                $isLate = $loan->isLate();
                                $lateDays = $loan->status === LoanStatus::Dikembalikan
                                    ? $loan->late_days
                                    : ($isLate && $loan->due_date ? max(0, Carbon::now()->startOfDay()->diffInDays(Carbon::parse($loan->due_date)->startOfDay())) : 0);
                                $fineAmount = $loan->status === LoanStatus::Dikembalikan
                                    ? $loan->fine_amount
                                    : ($lateDays * 100000);
                            @endphp
                            <tr class="hover:bg-[#141B24] transition">
                                <!-- Anggota / Unit -->
                                <td class="py-4 px-6 font-semibold text-white">
                                    <div>{{ $member->profile?->full_name ?? $member->username }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                        {{ $unit?->unit_code }} &middot; {{ $equipment?->name }}
                                    </div>
                                </td>

                                <!-- Tenggat -->
                                <td class="py-4 px-6 text-slate-300">
                                    <div>{{ $loan->due_date ? Carbon::parse($loan->due_date)->translatedFormat('d M Y') : '-' }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        @if ($loan->status === LoanStatus::Dikembalikan)
                                            Kembali {{ $loan->return_date ? Carbon::parse($loan->return_date)->translatedFormat('d M') : '-' }}
                                        @else
                                            Pinjam {{ $loan->borrow_date ? Carbon::parse($loan->borrow_date)->translatedFormat('d M') : '-' }}
                                        @endif
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-6">
                                    @if ($loan->status === LoanStatus::Dikembalikan)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-800/80 text-slate-300 border border-slate-700/60">
                                            Dikembalikan
                                        </span>
                                    @elseif ($isLate && $lateDays > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-950/70 text-rose-300 border border-rose-800/50">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-950/70 text-emerald-400 border border-emerald-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Dipinjam
                                        </span>
                                    @endif
                                </td>

                                <!-- Keterlambatan / Denda -->
                                <td class="py-4 px-6">
                                    @if ($lateDays > 0)
                                        <div class="text-slate-200 font-medium">{{ $lateDays }} hari &middot; Rp{{ number_format($fineAmount, 0, ',', '.') }}</div>
                                        <div class="text-[10px] {{ $loan->fine_paid ? 'text-emerald-400' : 'text-rose-400' }}">
                                            {{ $loan->fine_paid ? 'Lunas' : 'Belum dibayar' }}
                                        </div>
                                    @else
                                        <div class="text-slate-300 font-medium">0 hari &middot; Rp0</div>
                                    @endif
                                </td>

                                <!-- Tindakan -->
                                <td class="py-4 px-6 text-right">
                                    @if ($loan->status === LoanStatus::Aktif)
                                        <button
                                            type="button"
                                            wire:click="processReturn({{ $loan->id }})"
                                            class="px-3.5 py-1.5 rounded-xl font-medium text-xs bg-[#182330] hover:bg-[#202E3F] text-slate-200 border border-[#253648] transition cursor-pointer"
                                        >
                                            Catat Pengembalian
                                        </button>
                                    @elseif ($loan->status === LoanStatus::Dikembalikan && ! $loan->fine_paid && $loan->fine_amount > 0)
                                        <button
                                            type="button"
                                            wire:click="markPaid({{ $loan->id }})"
                                            class="px-3.5 py-1.5 rounded-xl font-medium text-xs bg-[#182330] hover:bg-[#202E3F] text-amber-300 border border-amber-800/50 transition cursor-pointer"
                                        >
                                            Tandai Denda Lunas
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-500">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">
                                    Tidak ada pinjaman yang sedang aktif atau membutuhkan tindak lanjut.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[#182330] text-xs text-slate-500">
                Denda keterlambatan: Rp100.000 / hari. Pelunasan dicatat setelah pembayaran diterima.
            </div>
        </div>
    </div>

    <!-- ================= CUSTOM MINIMALIST REJECT CONFIRMATION MODAL ================= -->
    @if ($rejectingLoanId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="cancelReject"></div>
            <div class="relative bg-[#111720] border border-[#1E2B38] rounded-2xl p-6 sm:p-7 max-w-sm w-full space-y-4 shadow-2xl z-10 text-center animate-in fade-in zoom-in-95 duration-200">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 mx-auto flex items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-white tracking-tight">Konfirmasi Tolak Pengajuan</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Tolak pengajuan pinjaman unit <strong class="text-white font-mono">{{ $rejectingUnitCode }}</strong> untuk anggota <strong class="text-white">{{ $rejectingMemberName }}</strong>?
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="cancelReject"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="reject({{ $rejectingLoanId }})"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white transition shadow-lg shadow-rose-900/30 cursor-pointer"
                    >
                        Ya, Tolak
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
