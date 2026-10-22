<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Cetak Riwayat Peminjaman - GhostGear Rental</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            html, body {
                background-color: #0B0F14 !important;
                color: #EBF2F8 !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-card {
                background-color: #111720 !important;
                border: 1px solid #1E2B38 !important;
                color: #EBF2F8 !important;
                box-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-text-white {
                color: #ffffff !important;
            }
            .print-text-slate {
                color: #94a3b8 !important;
            }
            .print-box {
                background-color: #141C26 !important;
                border: 1px solid #1E2B38 !important;
                color: #EBF2F8 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            table {
                background-color: #111720 !important;
                color: #EBF2F8 !important;
            }
            tr {
                border-color: #182330 !important;
            }
        }
    </style>
</head>
<body class="bg-[#0B0F14] text-[#EBF2F8] antialiased min-h-screen py-10 px-4 sm:px-8">

    <!-- Action Bar (Hidden when printing) -->
    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('admin.history') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Riwayat
        </a>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl font-bold text-xs bg-[#00FF66] text-black hover:bg-[#00E65C] transition flex items-center gap-2 shadow-[0_2px_12px_rgba(0,255,102,0.25)] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Printable Preview Card matching Preview Cetak Riwayat Peminjaman.png -->
    <div class="max-w-5xl mx-auto bg-[#111720] border border-[#1E2B38] rounded-3xl p-8 sm:p-12 shadow-2xl space-y-10 print-card">

        <!-- Top Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-8 border-b border-[#1A2533]">
            <img src="{{ asset('images/logo.png') }}" alt="GhostGear Rental" class="h-10 sm:h-12 w-auto object-contain">

            <div class="text-left sm:text-right">
                <h1 class="text-2xl font-extrabold text-white print-text-white tracking-tight">Riwayat Peminjaman</h1>
                <p class="text-xs text-slate-400 print-text-slate mt-1 font-medium">Periode {{ now()->translatedFormat('F Y') }}</p>
            </div>
        </div>

        @php
            $completedLoans = $loans->filter(fn($l) => $l->status === \App\Enums\LoanStatus::Dikembalikan);
            $uniqueMembers = $completedLoans->pluck('member_id')->unique()->count();
            $totalRentalPaid = $completedLoans->where('fine_paid', true)->sum(fn($l) => (float) $l->rental_fee);
            $totalFinePaid = $completedLoans->where('fine_paid', true)->sum('fine_amount');
            $totalFineUnpaid = $completedLoans->where('fine_paid', false)->sum('fine_amount');
            $totalConfirmed = $totalRentalPaid + $totalFinePaid;
        @endphp

        <!-- History Table -->
        <div class="border border-[#1A2533] rounded-2xl overflow-hidden print-box">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#1A2533] text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-[#131A24]/40 print-box">
                            <th class="py-3.5 px-6">Nama Anggota</th>
                            <th class="py-3.5 px-6">Jenis Alat</th>
                            <th class="py-3.5 px-6">Tgl Pinjam</th>
                            <th class="py-3.5 px-6">Tgl Kembali</th>
                            <th class="py-3.5 px-6">Denda</th>
                            <th class="py-3.5 px-6">Status Lunas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#182330]">
                        @forelse ($completedLoans as $loan)
                            @php
                                $member = $loan->member;
                                $unit = $loan->unit;
                                $equipment = $unit?->equipment;
                                $isPaid = $loan->fine_amount == 0 || $loan->fine_paid;
                            @endphp
                            <tr class="hover:bg-[#141B24] transition">
                                <td class="py-4 px-6 font-semibold text-white print-text-white">
                                    {{ $member?->profile?->full_name ?? $member?->username }}
                                </td>
                                <td class="py-4 px-6 text-slate-300 print-text-slate">
                                    {{ $equipment?->name ?? 'Peralatan' }}
                                </td>
                                <td class="py-4 px-6 text-slate-300 print-text-slate">
                                    {{ $loan->borrow_date ? \Carbon\Carbon::parse($loan->borrow_date)->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td class="py-4 px-6 text-slate-300 print-text-slate">
                                    {{ $loan->return_date ? \Carbon\Carbon::parse($loan->return_date)->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td class="py-4 px-6 font-medium text-slate-300 print-text-slate">
                                    Rp{{ number_format($loan->fine_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-xs font-semibold {{ $isPaid ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $isPaid ? 'Lunas' : 'Belum lunas' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">
                                    Belum ada data riwayat peminjaman yang selesai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ringkasan Penerimaan -->
        <div class="space-y-4 pt-4">
            <h2 class="text-base font-bold text-white print-text-white">Ringkasan Penerimaan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-[#141C26] border border-[#1E2B38] rounded-2xl p-6 print-box">
                    <p class="text-[11px] text-slate-400 font-medium">Jumlah anggota peminjam unik</p>
                    <div class="text-3xl font-black text-white print-text-white mt-2">{{ $uniqueMembers }}</div>
                </div>

                <!-- Card 2 -->
                <div class="bg-[#141C26] border border-[#1E2B38] rounded-2xl p-6 print-box">
                    <p class="text-[11px] text-slate-400 font-medium">Pendapatan sewa (transaksi lunas)</p>
                    <div class="text-2xl font-black text-white print-text-white mt-2">
                        Rp{{ number_format($totalRentalPaid, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-[#141C26] border border-[#1E2B38] rounded-2xl p-6 print-box">
                    <p class="text-[11px] text-slate-400 font-medium">Denda diterima</p>
                    <div class="text-2xl font-black text-white print-text-white mt-2">
                        Rp{{ number_format($totalFinePaid, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Notes List -->
            <div class="pt-4 space-y-1 text-xs text-slate-400 print-text-slate leading-relaxed">
                <p>Total penerimaan terkonfirmasi: <strong class="text-white print-text-white">Rp{{ number_format($totalConfirmed, 0, ',', '.') }}</strong></p>
                <p>Denda belum lunas: <strong class="text-rose-400">Rp{{ number_format($totalFineUnpaid, 0, ',', '.') }}</strong></p>
                <p class="text-[11px] text-slate-500 pt-1">Pendapatan dihitung dari transaksi berstatus Lunas.</p>
                <p class="text-[11px] text-slate-500">Tgl Kembali adalah tanggal pengembalian aktual yang tercatat dalam arsip.</p>
            </div>
        </div>

    </div>

</body>
</html>
