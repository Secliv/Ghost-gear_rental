<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\LoanPrintController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === UserRole::Admin
            ? redirect('/admin')
            : redirect()->route('catalog');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/loans/print', LoanPrintController::class)->name('admin.loans.print');
});

Route::middleware(['auth', 'role:anggota'])->group(function () {
    Volt::route('katalog', 'pages.catalog')->name('catalog');
    Volt::route('katalog/{equipment}', 'pages.equipment-detail')->name('equipment.detail');
    Volt::route('pinjaman-saya', 'pages.my-loans')->name('member.loans');
    Volt::route('profil-saya', 'pages.member-profile')->name('member.profile');
});

require __DIR__.'/auth.php';
