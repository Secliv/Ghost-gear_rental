<?php

use App\Http\Controllers\Admin\LoanPrintController;
use App\Livewire\Actions\Logout;
use App\Models\Equipment;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    $equipments = Equipment::with(['categories', 'units'])->get();

    return view('welcome', compact('equipments'));
})->name('landing');

Route::post('logout', function (Logout $logout) {
    $logout();

    return redirect()->route('login');
})->name('logout');

// ================= ADMIN ROUTES =================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin', fn () => redirect()->route('admin.approval'));
    Volt::route('admin/persetujuan', 'pages.admin.loans-approval')->name('admin.approval');
    Volt::route('admin/riwayat', 'pages.admin.loans-history')->name('admin.history');
    Route::get('admin/loans/print', LoanPrintController::class)->name('admin.loans.print');
    Volt::route('admin/alat-unit', 'pages.admin.equipments-manage')->name('admin.equipments');
    Volt::route('admin/kategori', 'pages.admin.categories-manage')->name('admin.categories');
    Volt::route('admin/anggota', 'pages.admin.members-manage')->name('admin.members');
    Volt::route('admin/profil', 'pages.admin.profile-manage')->name('admin.profile');
});

// ================= ANGGOTA ROUTES =================
Route::middleware(['auth', 'role:anggota'])->group(function () {
    Volt::route('katalog', 'pages.catalog')->name('catalog');
    Route::get('detail-alat', function () {
        $firstEquipment = Equipment::query()->first();

        return $firstEquipment ? redirect()->route('equipment.detail', $firstEquipment) : redirect()->route('catalog');
    })->name('equipment.detail.first');
    Volt::route('katalog/{equipment}', 'pages.equipment-detail')->name('equipment.detail');
    Volt::route('pinjaman-saya', 'pages.my-loans')->name('member.loans');
    Volt::route('profil-saya', 'pages.member-profile')->name('member.profile');
    Volt::route('profil-saya/edit', 'pages.member-profile')->name('member.profile.edit');
});

require __DIR__.'/auth.php';
