<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GhostGear Rental — Sewa Peralatan Investigasi Paranormal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo-icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0B0F14] text-[#EBF2F8] antialiased selection:bg-[#00FF66] selection:text-black min-h-screen">

    <!-- Top Navigation -->
    <header class="sticky top-0 z-50 bg-[#0B0F14]/90 backdrop-blur-md border-b border-[#1A232E]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="GhostGear Rental" class="h-10 sm:h-12 w-auto object-contain transition duration-200 group-hover:brightness-110">
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-300">
                <a href="#peralatan" class="hover:text-white transition">Peralatan</a>
                <a href="#cara-meminjam" class="hover:text-white transition">Cara Meminjam</a>
                <a href="#ketentuan" class="hover:text-white transition">Ketentuan</a>
                <a href="#faq" class="hover:text-white transition">FAQ</a>
            </nav>

            <!-- CTA / Auth Button -->
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.approval') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-xs font-bold bg-[#00FF66] text-black hover:bg-[#00E65C] transition shadow-[0_0_15px_rgba(0,255,102,0.25)]">
                                Dashboard Admin &rarr;
                            </a>
                        @else
                            <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-xs font-bold bg-[#00FF66] text-black hover:bg-[#00E65C] transition shadow-[0_0_15px_rgba(0,255,102,0.25)]">
                                Buka Katalog &rarr;
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-bold bg-[#00FF66] text-black hover:bg-[#00E65C] transition shadow-[0_0_15px_rgba(0,255,102,0.25)]">
                            Masuk
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <main class="space-y-24 sm:space-y-32 py-12 sm:py-20">

        <!-- 1. Hero Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                <!-- Left: Headline & Actions -->
                <div class="lg:col-span-6 space-y-6">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-400">
                        Sewa Peralatan Investigasi
                    </p>
                    <h1 class="text-4xl sm:text-6xl font-black tracking-tight text-white leading-[1.08]">
                        Buru yang tak terlihat.<br>
                        Bersama GhostGear.
                    </h1>
                    <p class="text-base sm:text-lg text-slate-400 leading-relaxed max-w-xl">
                        Sewa kamera termal, night vision, detektor EMF, perekam audio, dan sensor gerak untuk pengamatan lapangan. Pilih alat sesuai kebutuhan, ajukan pinjaman, dan siapkan kelengkapan sebelum berangkat.
                    </p>
                    <div class="flex flex-wrap items-center gap-5 pt-3">
                        <a href="#peralatan" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl font-bold text-sm bg-[#00FF66] text-black hover:bg-[#00E65C] transition shadow-[0_4px_20px_rgba(0,255,102,0.25)]">
                            Cari Alat
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                        </a>
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-[#00FF66] transition flex items-center gap-1.5">
                            Masuk ke akun
                        </a>
                    </div>
                </div>

                <!-- Right: Featured Product Showcase Card with 2s Auto Slider (No buttons) -->
                <div class="lg:col-span-6">
                    <div class="relative bg-[#111720] border border-[#1E2B38] rounded-2xl overflow-hidden p-6 sm:p-8 shadow-2xl select-none" id="hero-showcase-card">
                        <!-- Top Tag & Live Counter (No buttons) -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-300">
                                Alat Best Seller
                            </span>
                            <span class="text-xs font-mono text-slate-400 bg-[#16212D] border border-[#202F40] px-2.5 py-0.5 rounded-full" id="slide-counter-badge">
                                1 / {{ max(1, $equipments->count()) }}
                            </span>
                        </div>

                        <!-- Image Slider Container (Directly rendered images - first image immediately visible!) -->
                        <div class="relative h-64 sm:h-80 w-full flex items-center justify-center my-4 overflow-hidden">
                            @forelse ($equipments as $idx => $eq)
                                @php
                                    $img = $eq->image ? asset($eq->image) : asset('images/kamera-termal.png');
                                @endphp
                                <div class="hero-slide absolute inset-0 flex items-center justify-center transition-all duration-700 ease-out {{ $idx === 0 ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-12 pointer-events-none' }}" data-index="{{ $idx }}">
                                    <img src="{{ $img }}" alt="{{ $eq->name }}" class="h-60 sm:h-76 w-auto max-w-full object-contain drop-shadow-[0_15px_35px_rgba(0,0,0,0.85)]">
                                </div>
                            @empty
                                <div class="hero-slide absolute inset-0 flex items-center justify-center opacity-100 translate-x-0" data-index="0">
                                    <img src="{{ asset('images/kamera-termal.png') }}" alt="Kamera Termal" class="h-60 sm:h-76 w-auto max-w-full object-contain drop-shadow-[0_15px_35px_rgba(0,0,0,0.85)]">
                                </div>
                            @endforelse
                        </div>

                        <!-- Smooth Auto Progress Line (No buttons) -->
                        <div class="w-full bg-[#182330] h-1 rounded-full overflow-hidden mb-3">
                            <div class="bg-[#00FF66] h-full transition-all duration-500 ease-out rounded-full" id="slide-progress-bar" style="width: {{ $equipments->count() > 0 ? (100 / $equipments->count()) : 100 }}%;"></div>
                        </div>

                        <!-- Bottom labels with Dynamic Name & Price (Updates synchronously) -->
                        @php
                            $firstEq = $equipments->first();
                        @endphp
                        <div class="flex items-center justify-between text-xs text-slate-400 border-t border-[#1C2836] pt-4 mt-2">
                            <div>
                                <div class="font-bold text-white text-sm transition-opacity duration-300" id="slide-item-name">{{ $firstEq?->name ?? 'Kamera Termal' }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 transition-opacity duration-300" id="slide-item-category">{{ $firstEq?->categories->pluck('name')->join(' &bull; ') ?: 'Imaging' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-extrabold text-[#00FF66] text-sm transition-opacity duration-300" id="slide-item-price">Rp {{ number_format($firstEq?->rental_price_per_day ?: 0, 0, ',', '.') }} / hari</div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-wider font-mono">Tarif Sewa</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. Categories Quick Info (Pilih alat sesuai kebutuhan) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">
                    Pilih Alat Sesuai Kebutuhan
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Imaging -->
                    <div class="bg-[#111720] border border-[#1A2533] p-6 rounded-2xl space-y-4">
                        <div class="flex items-center gap-2.5 text-white font-semibold">
                            <svg class="w-5 h-5 text-[#00FF66]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Imaging
                        </div>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Gunakan untuk melihat perubahan suhu, pantau kondisi minim cahaya, dan dokumentasikan temuan lapangan.
                        </p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <span class="px-2.5 py-1 text-xs rounded-md bg-[#16212E] text-slate-300 border border-[#223142]">Kamera Termal</span>
                            <span class="px-2.5 py-1 text-xs rounded-md bg-[#16212E] text-slate-300 border border-[#223142]">Night Vision</span>
                        </div>
                    </div>

                    <!-- Pengukuran -->
                    <div class="bg-[#111720] border border-[#1A2533] p-6 rounded-2xl space-y-4">
                        <div class="flex items-center gap-2.5 text-white font-semibold">
                            <svg class="w-5 h-5 text-[#00FF66]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Pengukuran
                        </div>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Pantau medan elektromagnetik di lokasi investigasi untuk memperkuat data lapangan.
                        </p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <span class="px-2.5 py-1 text-xs rounded-md bg-[#16212E] text-slate-300 border border-[#223142]">Detektor EMF</span>
                        </div>
                    </div>

                    <!-- Perekaman & Deteksi -->
                    <div class="bg-[#111720] border border-[#1A2533] p-6 rounded-2xl space-y-4">
                        <div class="flex items-center gap-2.5 text-white font-semibold">
                            <svg class="w-5 h-5 text-[#00FF66]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                            Perekaman & deteksi
                        </div>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Rekam audio untuk EVP dan pantau pergerakan menggunakan sensor gerak untuk memperkaya dokumentasi.
                        </p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <span class="px-2.5 py-1 text-xs rounded-md bg-[#16212E] text-slate-300 border border-[#223142]">Perekam Audio</span>
                            <span class="px-2.5 py-1 text-xs rounded-md bg-[#16212E] text-slate-300 border border-[#223142]">Sensor Gerak</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Peralatan Grid Section -->
        <section id="peralatan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500 mb-2">Peralatan</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Pilih alat yang sesuai untuk<br>kebutuhan Anda.
                    </h2>
                </div>
                <p class="text-sm text-slate-400 max-w-sm">
                    Lihat peralatan yang tersedia beserta tarif sewa harian. Anda akan memilih jenis alat, bukan unit fisik tertentu.
                </p>
            </div>

            <!-- Dynamic Equipment Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($equipments as $eq)
                    @php
                        $img = $eq->image ? asset($eq->image) : asset('images/kamera-termal.png');
                        $targetUrl = auth()->check() ? route('equipment.detail', $eq) : route('login');
                    @endphp
                    <a href="{{ $targetUrl }}" class="bg-[#111720] border border-[#1A2533] hover:border-[#00FF66]/50 rounded-2xl overflow-hidden p-6 flex flex-col justify-between group transition duration-300 hover:shadow-[0_8px_30px_rgba(0,255,102,0.08)]">
                        <div class="h-48 sm:h-52 flex items-center justify-center p-3">
                            <img src="{{ $img }}" alt="{{ $eq->name }}" class="max-h-full max-w-full object-contain transition duration-300 group-hover:scale-105">
                        </div>
                        <div class="space-y-3 pt-4 border-t border-[#182330]">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($eq->categories as $c)
                                    <span class="text-[11px] px-2 py-0.5 rounded bg-[#16212E] text-slate-300 border border-[#243547] inline-block">{{ $c->name }}</span>
                                @endforeach
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white group-hover:text-[#00FF66] transition">{{ $eq->name }}</h3>
                                <p class="text-base font-bold text-white mt-1">
                                    Rp{{ number_format($eq->rental_price_per_day, 0, ',', '.') }}
                                    <span class="text-xs font-normal text-slate-400">/ hari</span>
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-500">
                        Belum ada peralatan yang tersedia.
                    </div>
                @endforelse
            </div>

            <p class="text-xs text-slate-500 mt-6 text-center">
                Tarif di atas adalah biaya sewa per hari. Ketersediaan alat diperiksa saat Anda mengajukan pinjaman.
            </p>
        </section>

        <!-- 4. Cara Meminjam Section -->
        <section id="cara-meminjam" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-[#182432] pt-20">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500 mb-2">Cara Meminjam</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Langkah-langkah peminjaman alat.
                    </h2>
                </div>
                <p class="text-sm text-slate-400 max-w-sm">
                    Anda memilih jenis alat dan tanggal peminjaman. Admin akan menentukan unit fisik yang akan diserahkan.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Step 01 -->
                <div class="bg-[#111720] border border-[#1A2533] p-6 rounded-2xl space-y-3">
                    <span class="text-xs font-bold text-[#00FF66] font-mono">01</span>
                    <h3 class="text-base font-bold text-white">Masuk ke akun</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Gunakan akun yang sudah didaftarkan oleh admin untuk melanjutkan ke katalog alat.
                    </p>
                </div>

                <!-- Step 02 -->
                <div class="bg-[#111720] border border-[#1A2533] p-6 rounded-2xl space-y-3">
                    <span class="text-xs font-bold text-[#00FF66] font-mono">02</span>
                    <h3 class="text-base font-bold text-white">Pilih jenis alat dan tanggal</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Tentukan jenis alat yang ingin Anda sewa dan tanggal peminjaman sesuai rencana lapangan.
                    </p>
                </div>

                <!-- Step 03 -->
                <div class="bg-[#111720] border border-[#1A2533] p-6 rounded-2xl space-y-3">
                    <span class="text-xs font-bold text-[#00FF66] font-mono">03</span>
                    <h3 class="text-base font-bold text-white">Ajukan pinjaman</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Periksa kembali pilihan alat dan tanggal sebelum Anda mengirimkan pengajuan.
                    </p>
                </div>

                <!-- Step 04 -->
                <div class="bg-[#111720] border border-[#1A2533] p-6 rounded-2xl space-y-3">
                    <span class="text-xs font-bold text-[#00FF66] font-mono">04</span>
                    <h3 class="text-base font-bold text-white">Persetujuan & serah terima</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Tunggu persetujuan admin, lalu lakukan serah terima alat sesuai jadwal yang disepakati.
                    </p>
                </div>
            </div>
        </section>

        <!-- 5. Ketentuan Utama Section -->
        <section id="ketentuan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-[#182432] pt-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <!-- Left Title -->
                <div class="lg:col-span-5 space-y-2">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">Ketentuan Utama</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Ketentuan peminjaman<br>untuk Anda.
                    </h2>
                </div>

                <!-- Right Stats Grid -->
                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="bg-[#111720] border border-[#1A2533] p-6 rounded-2xl">
                        <div class="text-4xl font-black text-white mb-2">2 <span class="text-lg font-medium text-slate-400">unit</span></div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Anda bisa menyewa maksimal 2 unit aktif dalam satu waktu.
                        </p>
                    </div>

                    <div class="bg-[#111720] border border-[#1A2533] p-6 rounded-2xl">
                        <div class="text-4xl font-black text-white mb-2">5 <span class="text-lg font-medium text-slate-400">hari</span></div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Maksimal durasi peminjaman untuk satu kali sewa adalah 5 hari.
                        </p>
                    </div>

                    <div class="bg-[#111720] border border-[#1A2533] p-6 rounded-2xl relative overflow-hidden">
                        <span class="text-[10px] uppercase tracking-wider text-rose-400 font-bold block mb-1">Denda Keterlambatan</span>
                        <div class="text-2xl font-black text-rose-300 mb-2">Rp100.000 <span class="text-xs font-normal text-slate-400">/ hari</span></div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Berlaku jika Anda mengembalikan alat terlambat. Denda ini terpisah dari biaya sewa harian.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. Periksa Bersama Section (Checklist) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center bg-[#111720] border border-[#1A2533] rounded-3xl p-6 sm:p-10">
                <!-- Left: Product image + kit checklist -->
                <div class="lg:col-span-6 bg-[#0B0F14] border border-[#1E2B38] rounded-2xl p-6 flex flex-col justify-between">
                    <div class="h-60 sm:h-72 flex items-center justify-center p-4">
                        <img src="{{ asset('images/kamera-termal.png') }}" alt="Kelengkapan Alat" class="max-h-full max-w-full object-contain">
                    </div>
                    <div class="pt-4 border-t border-[#182330]">
                        <p class="text-[10px] uppercase tracking-widest text-slate-500 font-semibold mb-2">Contoh Kelengkapan Kamera Termal</p>
                        <div class="flex items-center gap-4 text-xs font-medium text-slate-300">
                            <span class="flex items-center gap-1.5"><span class="text-[#00FF66]">&check;</span> Baterai</span>
                            <span class="flex items-center gap-1.5"><span class="text-[#00FF66]">&check;</span> Charger</span>
                            <span class="flex items-center gap-1.5"><span class="text-[#00FF66]">&check;</span> Hard case</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Rules & Tips -->
                <div class="lg:col-span-6 space-y-6 lg:pl-6">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">Sebelum Berangkat</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Periksa bersama.<br>Bawa dengan siap.
                    </h2>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Saat serah terima, cocokkan alat dan kelengkapannya bersama admin agar kondisi awal jelas sebelum digunakan.
                    </p>
                    <ul class="space-y-3 text-sm text-slate-300">
                        <li class="flex items-start gap-3">
                            <span class="text-[#00FF66] mt-0.5">•</span>
                            <span>Cek kondisi fisik dan fungsi alat.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-[#00FF66] mt-0.5">•</span>
                            <span>Cocokkan baterai, charger, dan hard case.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-[#00FF66] mt-0.5">•</span>
                            <span>Pastikan tanggal pengembalian sudah sesuai.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 7. FAQ Section -->
        <section id="faq" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-[#182432] pt-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-5 space-y-3">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">Pertanyaan Umum</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Hal penting sebelum meminjam.
                    </h2>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Ringkasan tentang akses akun, pengajuan, dan pengembalian alat.
                    </p>
                </div>

                <div class="lg:col-span-7 space-y-4" x-data="{ active: null }">
                    <!-- Q1 -->
                    <div class="border-b border-[#1E2B38] pb-4">
                        <button @click="active = active === 1 ? null : 1" class="w-full text-left flex justify-between items-center py-2 text-sm font-semibold text-white hover:text-[#00FF66] transition">
                            <span>Bagaimana cara mendapatkan akun?</span>
                            <span x-text="active === 1 ? '−' : '+'" class="text-lg text-slate-400"></span>
                        </button>
                        <div x-show="active === 1" x-collapse class="pt-2 text-xs sm:text-sm text-slate-400 leading-relaxed">
                            Akun anggota didaftarkan oleh admin GhostGear Rental. Tidak ada pendaftaran mandiri di halaman ini.
                        </div>
                    </div>

                    <!-- Q2 -->
                    <div class="border-b border-[#1E2B38] pb-4">
                        <button @click="active = active === 2 ? null : 2" class="w-full text-left flex justify-between items-center py-2 text-sm font-semibold text-white hover:text-[#00FF66] transition">
                            <span>Apakah saya memilih unit fisik tertentu?</span>
                            <span x-text="active === 2 ? '−' : '+'" class="text-lg text-slate-400"></span>
                        </button>
                        <div x-show="active === 2" x-collapse class="pt-2 text-xs sm:text-sm text-slate-400 leading-relaxed">
                            Tidak. Anda memilih jenis alat dan tanggal peminjaman. Admin yang menentukan unit fisik untuk pengajuan Anda.
                        </div>
                    </div>

                    <!-- Q3 -->
                    <div class="border-b border-[#1E2B38] pb-4">
                        <button @click="active = active === 3 ? null : 3" class="w-full text-left flex justify-between items-center py-2 text-sm font-semibold text-white hover:text-[#00FF66] transition">
                            <span>Apakah pengajuan langsung disetujui?</span>
                            <span x-text="active === 3 ? '−' : '+'" class="text-lg text-slate-400"></span>
                        </button>
                        <div x-show="active === 3" x-collapse class="pt-2 text-xs sm:text-sm text-slate-400 leading-relaxed">
                            Setiap pengajuan memerlukan persetujuan admin sebelum dilanjutkan ke serah terima alat.
                        </div>
                    </div>

                    <!-- Q4 -->
                    <div class="border-b border-[#1E2B38] pb-4">
                        <button @click="active = active === 4 ? null : 4" class="w-full text-left flex justify-between items-center py-2 text-sm font-semibold text-white hover:text-[#00FF66] transition">
                            <span>Berapa lama alat boleh dipinjam?</span>
                            <span x-text="active === 4 ? '−' : '+'" class="text-lg text-slate-400"></span>
                        </button>
                        <div x-show="active === 4" x-collapse class="pt-2 text-xs sm:text-sm text-slate-400 leading-relaxed">
                            Durasi maksimal adalah 5 hari, dengan batas 2 unit aktif per anggota.
                        </div>
                    </div>

                    <!-- Q5 -->
                    <div class="border-b border-[#1E2B38] pb-4">
                        <button @click="active = active === 5 ? null : 5" class="w-full text-left flex justify-between items-center py-2 text-sm font-semibold text-white hover:text-[#00FF66] transition">
                            <span>Bagaimana jika pengembalian terlambat?</span>
                            <span x-text="active === 5 ? '−' : '+'" class="text-lg text-slate-400"></span>
                        </button>
                        <div x-show="active === 5" x-collapse class="pt-2 text-xs sm:text-sm text-slate-400 leading-relaxed">
                            Denda keterlambatan adalah Rp100.000 per hari. Denda ini terpisah dari biaya sewa harian alat.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. Bottom CTA Banner -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-[#111720] via-[#151D28] to-[#111720] border border-[#1E2B38] rounded-3xl p-8 sm:p-12 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-2xl">
                <div class="space-y-2 text-center sm:text-left">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">Mulai Dari Rencana Anda</p>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Siapkan pengamatan berikutnya.
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400">
                        Masuk untuk memilih alat dan mengajukan pinjaman. Akun didaftarkan oleh admin.
                    </p>
                </div>
                <a href="{{ route('login') }}" class="shrink-0 inline-flex items-center gap-2 px-8 py-3.5 rounded-xl font-bold text-sm bg-[#00FF66] text-black hover:bg-[#00E65C] transition shadow-[0_4px_20px_rgba(0,255,102,0.3)]">
                    Masuk
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                </a>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-[#182330] py-12 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6 text-xs text-slate-400">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="GhostGear Rental" class="h-8 w-auto object-contain">
            </div>

            <div class="flex items-center gap-6">
                <a href="#peralatan" class="hover:text-white transition">Peralatan</a>
                <a href="#cara-meminjam" class="hover:text-white transition">Cara Meminjam</a>
                <a href="#ketentuan" class="hover:text-white transition">Ketentuan</a>
                <a href="#faq" class="hover:text-white transition">FAQ</a>
            </div>

            <p>&copy; 2026 GhostGear Rental. All rights reserved.</p>
        </div>
    </footer>

    @php
        $slideMetaData = $equipments->map(function ($eq) {
            return [
                'name' => $eq->name,
                'category' => $eq->categories->pluck('name')->join(' &bull; ') ?: 'Imaging',
                'price' => 'Rp ' . number_format($eq->rental_price_per_day, 0, ',', '.') . ' / hari',
            ];
        })->values()->all();
    @endphp

    <!-- Smooth 2-Second Auto-Slide Animation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const slideMeta = {!! json_encode($slideMetaData) !!};

            const slides = document.querySelectorAll('.hero-slide');
            const nameEl = document.getElementById('slide-item-name');
            const catEl = document.getElementById('slide-item-category');
            const priceEl = document.getElementById('slide-item-price');
            const counterEl = document.getElementById('slide-counter-badge');
            const progressEl = document.getElementById('slide-progress-bar');

            if (!slides || slides.length === 0) return;

            let current = 0;
            const total = slides.length;

            setInterval(function () {
                const next = (current + 1) % total;

                // Animate current slide out smoothly to the left
                const curSlide = slides[current];
                curSlide.classList.remove('opacity-100', 'translate-x-0');
                curSlide.classList.add('opacity-0', '-translate-x-12', 'pointer-events-none');

                // Prepare next slide from right
                const nxtSlide = slides[next];
                nxtSlide.classList.remove('-translate-x-12', 'pointer-events-none');
                nxtSlide.classList.add('translate-x-12', 'opacity-0');

                // Trigger reflow to restart CSS transition
                void nxtSlide.offsetWidth;

                // Slide next in smoothly
                nxtSlide.classList.remove('translate-x-12');
                nxtSlide.classList.add('translate-x-0', 'opacity-100');

                // Update text with soft crossfade
                if (nameEl && catEl && priceEl) {
                    nameEl.style.opacity = '0';
                    catEl.style.opacity = '0';
                    priceEl.style.opacity = '0';

                    setTimeout(function () {
                        nameEl.textContent = slideMeta[next].name;
                        catEl.innerHTML = slideMeta[next].category;
                        priceEl.textContent = slideMeta[next].price;
                        if (counterEl) counterEl.textContent = (next + 1) + ' / ' + total;
                        if (progressEl) progressEl.style.width = (((next + 1) / total) * 100) + '%';

                        nameEl.style.opacity = '1';
                        catEl.style.opacity = '1';
                        priceEl.style.opacity = '1';
                    }, 200);
                }

                current = next;
            }, 2000); // Tepat 2 detik per pergantian slide
        });
    </script>
</body>
</html>
