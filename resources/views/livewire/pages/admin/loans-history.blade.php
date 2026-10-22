<?php

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $period = 'all';
    public string $memberId = 'all';
    public string $paymentStatus = 'all';

    public function with(): array
    {
        $query = Loan::query()
            ->with(['member.profile', 'unit.equipment'])
            ->where('status', LoanStatus::Dikembalikan);

        if ($this->period === 'current') {
            $query->whereMonth('return_date', now()->month)->whereYear('return_date', now()->year);
        }

        if ($this->memberId !== 'all') {
            $query->where('member_id', $this->memberId);
        }

        if ($this->paymentStatus === 'lunas') {
            $query->where(function ($q) {
                $q->where('fine_amount', 0)->orWhere('fine_paid', true);
            });
        } elseif ($this->paymentStatus === 'belum_lunas') {
            $query->where('fine_amount', '>', 0)->where('fine_paid', false);
        }

        $loans = $query->latest('return_date')->get();
        $members = User::query()->where('role', \App\Enums\UserRole::Anggota)->with('profile')->get();

        return [
            'loans' => $loans,
            'members' => $members,
        ];
    }
}; ?>

<div class="space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white mb-1">
                Riwayat Peminjaman
            </h1>
            <p class="text-sm text-slate-400">
                Catatan sewa yang telah dikembalikan beserta pembayaran dan denda.
            </p>
        </div>

        <a href="{{ route('admin.loans.print') }}" target="_blank"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-[#00FF66] text-black hover:bg-[#00E65C] transition shadow-[0_2px_12px_rgba(0,255,102,0.25)]">
            <span>Cetak ke PDF / Print</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Periode Kembali -->
        <div class="space-y-1.5">
            <label class="block text-xs font-medium text-slate-400">Periode kembali</label>
            <select
                wire:model.live="period"
                class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#00FF66] transition cursor-pointer"
            >
                <option value="all">Semua Periode</option>
                <option value="current">{{ now()->translatedFormat('F Y') }}</option>
            </select>
        </div>

        <!-- Anggota -->
        <div class="space-y-1.5">
            <label class="block text-xs font-medium text-slate-400">Anggota</label>
            <select
                wire:model.live="memberId"
                class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#00FF66] transition cursor-pointer"
            >
                <option value="all">Semua anggota</option>
                @foreach ($members as $mem)
                    <option value="{{ $mem->id }}">
                        {{ $mem->profile?->full_name ?? $mem->username }} ({{ $mem->profile?->member_code ?? 'AGT' }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Pembayaran -->
        <div class="space-y-1.5">
            <label class="block text-xs font-medium text-slate-400">Pembayaran</label>
            <select
                wire:model.live="paymentStatus"
                class="w-full bg-[#131B23] border border-[#1D2A37] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-[#00FF66] transition cursor-pointer"
            >
                <option value="all">Semua status</option>
                <option value="lunas">Lunas</option>
                <option value="belum_lunas">Belum lunas</option>
            </select>
        </div>
    </div>

    <!-- History Table -->
    <div class="bg-[#111720] border border-[#1A2533] rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#1A2533] text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Anggota / Unit</th>
                        <th class="py-3.5 px-6">Periode / Kembali</th>
                        <th class="py-3.5 px-6">Biaya Sewa</th>
                        <th class="py-3.5 px-6">Denda</th>
                        <th class="py-3.5 px-6">Total</th>
                        <th class="py-3.5 px-6">Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#182330]">
                    @forelse ($loans as $loan)
                        @php
                            $member = $loan->member;
                            $unit = $loan->unit;
                            $equipment = $unit?->equipment;
                            $borrowDateStr = $loan->borrow_date ? Carbon::parse($loan->borrow_date)->translatedFormat('d') : '';
                            $dueDateStr = $loan->due_date ? Carbon::parse($loan->due_date)->translatedFormat('d M Y') : '';
                            $returnDateStr = $loan->return_date ? Carbon::parse($loan->return_date)->translatedFormat('d M') : '';
                            $lateNote = $loan->late_days > 0 ? "telat {$loan->late_days} hari" : "tepat waktu";
                            $rentalFee = $loan->rental_fee ?: ($equipment?->rental_price_per_day && $loan->borrow_date && $loan->due_date ? $equipment->rental_price_per_day * max(1, Carbon::parse($loan->borrow_date)->diffInDays(Carbon::parse($loan->due_date)) + 1) : 0);
                            $totalFee = $rentalFee + (float)$loan->fine_amount;
                            $isPaid = $loan->fine_amount == 0 || $loan->fine_paid;
                        @endphp
                        <tr class="hover:bg-[#141B24] transition">
                            <!-- Anggota / Unit -->
                            <td class="py-4 px-6 font-semibold text-white">
                                <div>{{ $member?->profile?->full_name ?? $member?->username }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $unit?->unit_code }} &middot; {{ $equipment?->name }}
                                </div>
                            </td>

                            <!-- Periode / Kembali -->
                            <td class="py-4 px-6 text-slate-300">
                                <div>{{ $borrowDateStr }} &ndash; {{ $dueDateStr }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    Kembali {{ $returnDateStr }} &middot; {{ $lateNote }}
                                </div>
                            </td>

                            <!-- Biaya Sewa -->
                            <td class="py-4 px-6 font-medium text-white">
                                Rp{{ number_format($rentalFee, 0, ',', '.') }}
                            </td>

                            <!-- Denda -->
                            <td class="py-4 px-6 font-medium text-white">
                                Rp{{ number_format($loan->fine_amount, 0, ',', '.') }}
                            </td>

                            <!-- Total -->
                            <td class="py-4 px-6 font-bold text-white">
                                Rp{{ number_format($totalFee, 0, ',', '.') }}
                            </td>

                            <!-- Pembayaran Status -->
                            <td class="py-4 px-6">
                                @if ($isPaid)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-800/80 text-slate-300 border border-slate-700/60">
                                        Lunas
                                    </span>
                                @else
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-950/70 text-rose-300 border border-rose-800/50">
                                            Belum lunas
                                        </span>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Denda belum dibayar</div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Tidak ada data riwayat peminjaman yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-[#182330] flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-400 gap-2">
            <span>Menampilkan {{ $loans->count() }} peminjaman selesai</span>
            <span class="text-slate-500">Total = biaya sewa + denda</span>
        </div>
    </div>
</div>
