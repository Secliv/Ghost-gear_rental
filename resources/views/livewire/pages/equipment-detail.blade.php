<?php

use App\Models\Equipment;
use App\Models\Loan;
use App\Models\Unit;
use App\Services\LoanService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Equipment $equipment;
    public string $borrowDate = '';
    public string $dueDate = '';
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    public function mount(Equipment $equipment): void
    {
        $this->equipment = $equipment->load(['categories', 'units']);
        $this->borrowDate = Carbon::now()->toDateString();
        $this->dueDate = Carbon::now()->addDays(3)->toDateString();
    }

    public function updatedBorrowDate(): void
    {
        $this->validateLoanDates();
    }

    public function updatedDueDate(): void
    {
        $this->validateLoanDates();
    }

    public function validateLoanDates(): void
    {
        $this->errorMessage = null;

        if (empty($this->borrowDate) || empty($this->dueDate)) {
            return;
        }

        try {
            $start = Carbon::parse($this->borrowDate)->startOfDay();
            $end = Carbon::parse($this->dueDate)->startOfDay();

            if ($start->lt(Carbon::today())) {
                $this->errorMessage = 'Tanggal pinjam tidak boleh sebelum hari ini.';
                return;
            }

            if ($end->lt($start)) {
                $this->errorMessage = 'Tanggal kembali tidak boleh lebih awal dari tanggal pinjam.';
                return;
            }

            $diff = $start->diffInDays($end);
            $days = $diff === 0 ? 1 : $diff;

            if ($days > 5) {
                $this->errorMessage = 'Peminjaman maksimal 5 hari!';
                $this->dispatch('loan-max-days-exceeded', [
                    'message' => 'Peminjaman maksimal 5 hari! Durasi yang Anda pilih: ' . $days . ' hari.',
                    'days' => $days,
                ]);
            }
        } catch (\Exception $e) {
            // ignore parse exceptions
        }
    }

    public function calculateDays(): int
    {
        try {
            if (empty($this->borrowDate) || empty($this->dueDate)) {
                return 1;
            }
            $start = Carbon::parse($this->borrowDate)->startOfDay();
            $end = Carbon::parse($this->dueDate)->startOfDay();
            if ($end->lt($start)) {
                return 1;
            }
            $diff = $start->diffInDays($end);
            return $diff === 0 ? 1 : $diff;
        } catch (\Exception $e) {
            return 1;
        }
    }

    public function requestLoan(LoanService $loanService)
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $user = auth()->user();

        if (! $user->is_active) {
            $this->errorMessage = 'Akun Anda dinonaktifkan. Silakan hubungi admin.';
            return;
        }

        // 1. Check active/pending loans count
        $activeCount = Loan::query()
            ->where('member_id', $user->id)
            ->whereIn('status', [\App\Enums\LoanStatus::Menunggu, \App\Enums\LoanStatus::Aktif])
            ->count();

        if ($activeCount >= 2) {
            $this->errorMessage = 'Anda sudah mencapai batas maksimal 2 unit pinjaman aktif/menunggu.';
            return;
        }

        // 2. Validate dates & 5-day duration
        $start = Carbon::parse($this->borrowDate)->startOfDay();
        $end = Carbon::parse($this->dueDate)->startOfDay();

        if ($start->lt(Carbon::today())) {
            $this->errorMessage = 'Tanggal pinjam tidak boleh sebelum hari ini.';
            return;
        }

        if ($end->lt($start)) {
            $this->errorMessage = 'Tanggal kembali tidak boleh sebelum tanggal pinjam.';
            return;
        }

        $diff = $start->diffInDays($end);
        $days = $diff === 0 ? 1 : $diff;

        if ($days > 5) {
            $this->errorMessage = 'Peminjaman maksimal 5 hari!';
            $this->dispatch('loan-max-days-exceeded', [
                'message' => 'Peminjaman maksimal 5 hari! Durasi yang Anda pilih: ' . $days . ' hari.',
                'days' => $days,
            ]);
            return;
        }

        // 3. Find available physical unit
        $availableUnit = $this->equipment->availableUnits()->first();
        if (! $availableUnit) {
            $this->errorMessage = 'Maaf, seluruh unit fisik untuk alat ini sedang tidak tersedia.';
            return;
        }

        try {
            $loan = $loanService->request($user, $availableUnit);

            $totalRental = $days * (float) $this->equipment->rental_price_per_day;

            $loan->update([
                'borrow_date' => $start,
                'due_date' => $end,
                'rental_fee' => $totalRental,
            ]);

            session()->flash('success', "Pengajuan peminjaman untuk {$this->equipment->name} (Kode: {$availableUnit->unit_code}) berhasil diajukan! Menunggu persetujuan admin.");

            return $this->redirect(route('member.loans'), navigate: true);
        } catch (\DomainException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal memproses pengajuan: ' . $e->getMessage();
        }
    }
}; ?>

<div
    class="space-y-8"
    x-data="{
        showConfirmLoanModal: false,
        showAlertModal: false,
        alertMessage: '',
        checkMaxDays() {
            const startEl = document.getElementById('borrowDateInput');
            const endEl = document.getElementById('dueDateInput');
            if (!startEl || !endEl || !startEl.value || !endEl.value) return;
            const start = new Date(startEl.value + 'T00:00:00');
            const end = new Date(endEl.value + 'T00:00:00');
            const diffTime = end.getTime() - start.getTime();
            const diffDays = Math.round(diffTime / (1000 * 3600 * 24));
            if (diffDays > 5) {
                this.triggerAlert('Peminjaman maksimal 5 hari! Durasi yang Anda pilih: ' + diffDays + ' hari.');
            }
        },
        triggerAlert(msg) {
            this.alertMessage = msg;
            this.showAlertModal = true;
        }
    }"
    @loan-max-days-exceeded.window="triggerAlert($event.detail.message || 'Peminjaman maksimal 5 hari!')"
>
    @php
        $availableUnitsCount = $this->equipment->availableUnits()->count();
        $days = $this->calculateDays();
        $dailyPrice = (float) $this->equipment->rental_price_per_day;
        $totalPrice = $days * $dailyPrice;

        $userActiveLoans = \App\Models\Loan::query()
            ->where('member_id', auth()->id())
            ->whereIn('status', [\App\Enums\LoanStatus::Menunggu, \App\Enums\LoanStatus::Aktif])
            ->count();
        $remainingSlots = max(0, 2 - $userActiveLoans);
        $imagePath = $this->equipment->image ? asset($this->equipment->image) : asset('images/kamera-termal.png');
    @endphp

    <!-- Alerts -->
    @if ($successMessage)
        <div class="p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-sm flex items-start gap-3 shadow-lg">
            <span class="text-base text-[#00FF66]">✓</span>
            <div>
                <p class="font-bold text-white">{{ $successMessage }}</p>
                <p class="text-xs text-emerald-300 mt-1">Anda dapat melihat status pengajuan di menu <a href="{{ route('member.loans') }}" class="underline hover:text-white">Pinjaman Saya</a>.</p>
            </div>
        </div>
    @endif

    @if ($errorMessage)
        <div class="p-4 rounded-xl bg-rose-950/80 border border-rose-500/50 text-rose-200 text-sm flex items-start gap-3 shadow-lg">
            <span class="text-base text-rose-400">⚠</span>
            <div>
                <p class="font-bold text-white">{{ $errorMessage }}</p>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-white mb-2">
                {{ $this->equipment->name }}
            </h1>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                @foreach ($this->equipment->categories as $category)
                    <span class="px-2.5 py-0.5 text-xs rounded-md bg-[#16212E] text-slate-300 border border-[#243547]">
                        {{ $category->name }}
                    </span>
                @endforeach
            </div>
            <p class="text-xs text-slate-400">
                {{ $this->equipment->description ?? 'Peralatan investigasi profesional siap pakai.' }}
            </p>
        </div>

        <div>
            @if ($availableUnitsCount > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-950/70 text-emerald-400 border border-emerald-800/50">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Tersedia ({{ $availableUnitsCount }} Unit)
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-950/70 text-rose-400 border border-rose-800/50">
                    Stok Tidak Tersedia
                </span>
            @endif
        </div>
    </div>

    <!-- 2 Column Layout: Specifications & Booking Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Left Column: Image & Specifications Table -->
        <div class="lg:col-span-7 space-y-8">
            <!-- Large Image Container -->
            <div class="bg-[#111720] border border-[#1A2533] rounded-2xl p-8 flex items-center justify-center min-h-[320px]">
                <img src="{{ $imagePath }}" alt="{{ $this->equipment->name }}" class="max-h-72 w-auto object-contain drop-shadow-[0_20px_35px_rgba(0,0,0,0.7)]">
            </div>

            <!-- Technical Specifications -->
            <div class="space-y-4">
                <h2 class="text-lg font-bold text-white">Spesifikasi teknis</h2>

                <div class="border border-[#1A2533] rounded-xl overflow-hidden divide-y divide-[#182330] text-xs">
                    @forelse ($this->equipment->specifications as $spec)
                        <div class="flex justify-between p-3.5 bg-[#111720]">
                            <span class="text-slate-400">{{ $spec['label'] }}</span>
                            <span class="font-medium text-white">{{ $spec['value'] }}</span>
                        </div>
                    @empty
                        <div class="p-3.5 bg-[#111720] text-slate-500">
                            Tidak ada spesifikasi khusus.
                        </div>
                    @endforelse
                </div>

                <p class="text-[11px] text-slate-500">
                    Spesifikasi teknis. Kelengkapan diperiksa saat serah terima.
                </p>
            </div>
        </div>

        <!-- Right Column: Booking Card -->
        <div class="lg:col-span-5">
            <div class="bg-[#111720] border border-[#1A2533] rounded-2xl p-6 sm:p-7 space-y-6 sticky top-28">
                <div>
                    <h2 class="text-lg font-bold text-white mb-1">Ajukan peminjaman</h2>
                    <p class="text-xs text-slate-400 font-mono">{{ $this->equipment->name }}</p>
                    <div class="text-2xl font-black text-white mt-2">
                        Rp{{ number_format($dailyPrice, 0, ',', '.') }}
                        <span class="text-xs font-normal text-slate-400">/ hari</span>
                    </div>
                </div>

                <!-- Date Inputs -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-xs text-slate-300 font-medium">Tanggal pinjam</label>
                            <input
                                type="date"
                                id="borrowDateInput"
                                wire:model.live="borrowDate"
                                @change="checkMaxDays()"
                                class="w-full bg-[#131B23] border {{ $days > 5 ? 'border-rose-500/70' : 'border-[#1D2A37]' }} rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-[#00FF66] transition"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs text-slate-300 font-medium">Tanggal kembali</label>
                            <input
                                type="date"
                                id="dueDateInput"
                                wire:model.live="dueDate"
                                @change="checkMaxDays()"
                                class="w-full bg-[#131B23] border {{ $days > 5 ? 'border-rose-500 text-rose-300' : 'border-[#1D2A37] text-white' }} rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-[#00FF66] transition"
                            >
                        </div>
                    </div>

                    @if ($days > 5)
                        <div class="p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-300 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span class="font-medium">Peminjaman maksimal 5 hari! (Anda memilih {{ $days }} hari)</span>
                        </div>
                    @else
                        <p class="text-[11px] text-slate-500">Durasi maksimal 5 hari.</p>
                    @endif
                </div>

                <!-- Live Calculation -->
                <div class="pt-4 border-t border-[#182330] space-y-3">
                    <div class="flex justify-between text-xs text-slate-400">
                        <span>{{ $days }} hari &times; Rp{{ number_format($dailyPrice, 0, ',', '.') }}</span>
                        <span>Rp{{ number_format($totalPrice, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-baseline pt-2 border-t border-[#182330]">
                        <span class="text-xs font-semibold text-slate-300">Total sewa</span>
                        <span class="text-xl font-black text-white">
                            Rp{{ number_format($totalPrice, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    @if ($days > 5)
                        <button
                            type="button"
                            @click="triggerAlert('Peminjaman maksimal 5 hari! Durasi yang Anda pilih saat ini: {{ $days }} hari.')"
                            class="w-full h-[48px] bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/40 text-rose-300 font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Peminjaman Maksimal 5 Hari</span>
                        </button>
                    @elseif ($availableUnitsCount === 0)
                        <button disabled class="w-full h-[48px] bg-slate-800 text-slate-500 font-bold text-sm rounded-xl cursor-not-allowed flex items-center justify-center">
                            Stok Unit Tidak Tersedia
                        </button>
                    @elseif ($remainingSlots === 0)
                        <button disabled class="w-full h-[48px] bg-slate-800 text-slate-500 font-bold text-sm rounded-xl cursor-not-allowed flex items-center justify-center">
                            Batas Maksimal 2 Unit Tercapai
                        </button>
                    @else
                        <button
                            type="button"
                            @click="showConfirmLoanModal = true"
                            wire:loading.attr="disabled"
                            class="w-full relative h-[48px] bg-[#00FF66] hover:bg-[#00E65C] disabled:opacity-85 disabled:cursor-wait active:scale-[0.99] text-black font-bold text-sm rounded-xl transition duration-150 shadow-[0_4px_16px_rgba(0,255,102,0.25)] flex items-center justify-center cursor-pointer overflow-hidden"
                        >
                            <span wire:loading.remove wire:target="requestLoan" class="inline-flex items-center justify-center gap-2">
                                <span>Ajukan Pinjam</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                            </span>
                            <span wire:loading.flex wire:target="requestLoan" class="items-center justify-center gap-2.5">
                                <svg class="animate-spin h-4 w-4 text-black shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="font-bold">Memproses...</span>
                            </span>
                        </button>
                    @endif

                    <p class="text-[11px] text-slate-500 text-center mt-3">
                        Pengajuan menunggu persetujuan admin.
                    </p>
                </div>

                <!-- Bottom Notes -->
                <div class="pt-4 border-t border-[#182330] space-y-1 text-[11px] text-slate-400">
                    <p>{{ $userActiveLoans }} dari 2 slot aktif terpakai &middot; {{ $remainingSlots }} slot tersedia</p>
                    <p>Denda keterlambatan Rp100.000 / hari.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= CUSTOM MINIMALIST CONFIRM LOAN MODAL ================= -->
    <div x-show="showConfirmLoanModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showConfirmLoanModal = false"></div>
        <div class="relative bg-[#111720] border border-[#1E2B38] rounded-2xl p-6 sm:p-7 max-w-sm w-full space-y-5 shadow-2xl z-10 text-center animate-in fade-in zoom-in-95 duration-200">
            <div class="w-12 h-12 rounded-2xl bg-[#00FF66]/10 border border-[#00FF66]/20 text-[#00FF66] mx-auto flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="space-y-1 text-left bg-[#0E141C] p-3.5 rounded-xl border border-[#182330] text-xs">
                <div class="font-bold text-white text-sm pb-1 border-b border-[#1A2533]">{{ $this->equipment->name }}</div>
                <div class="flex justify-between py-1 text-slate-300">
                    <span class="text-slate-400">Durasi Sewa:</span>
                    <span class="font-semibold text-white">{{ $days }} hari</span>
                </div>
                <div class="flex justify-between py-1 text-slate-300">
                    <span class="text-slate-400">Total Tarif:</span>
                    <span class="font-bold text-[#00FF66]">Rp{{ number_format($totalPrice, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-white tracking-tight">Konfirmasi Peminjaman</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Pastikan jadwal investigasi dan kelengkapan alat sudah sesuai sebelum mengajukan pinjaman.
                </p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-1">
                <button
                    type="button"
                    @click="showConfirmLoanModal = false"
                    class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#182330] hover:bg-[#202E3F] text-slate-300 border border-[#253648] transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="button"
                    wire:click="requestLoan"
                    @click="showConfirmLoanModal = false"
                    class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold bg-[#00FF66] hover:bg-[#00E65C] text-black transition shadow-lg shadow-emerald-950/50 cursor-pointer"
                >
                    Ya, Ajukan
                </button>
            </div>
        </div>
    </div>

    <!-- ================= CUSTOM MINIMALIST ALERT MODAL (Replaces browser alert) ================= -->
    <div x-show="showAlertModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showAlertModal = false"></div>
        <div class="relative bg-[#111720] border border-[#1E2B38] rounded-2xl p-6 sm:p-7 max-w-sm w-full space-y-4 shadow-2xl z-10 text-center animate-in fade-in zoom-in-95 duration-200">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 mx-auto flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-white tracking-tight">Perhatian</h3>
                <p class="text-xs text-slate-300 leading-relaxed" x-text="alertMessage"></p>
            </div>
            <div class="pt-2">
                <button
                    type="button"
                    @click="showAlertModal = false"
                    class="w-full px-4 py-2.5 rounded-xl text-xs font-bold bg-[#182330] hover:bg-[#202E3F] text-white border border-[#253648] transition cursor-pointer"
                >
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

</div>
