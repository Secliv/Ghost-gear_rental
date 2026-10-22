<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Enums\LoanStatus;
use App\Filament\Resources\LoanResource;
use App\Models\Loan;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLoans extends ListRecords
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak Riwayat')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(route('admin.loans.print'))
                ->openUrlInNewTab(),
            Actions\CreateAction::make()
                ->label('Buat Pinjaman Baru'),
        ];
    }

    public function getTabs(): array
    {
        $pendingCount = Loan::query()->where('status', LoanStatus::Menunggu->value)->count();
        $activeCount = Loan::query()->where('status', LoanStatus::Aktif->value)->count();

        return [
            'all' => Tab::make('Semua'),
            'menunggu' => Tab::make('Menunggu Persetujuan')
                ->badge($pendingCount > 0 ? (string) $pendingCount : null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LoanStatus::Menunggu->value)),
            'aktif' => Tab::make('Pinjaman Aktif')
                ->badge($activeCount > 0 ? (string) $activeCount : null)
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LoanStatus::Aktif->value)),
            'riwayat' => Tab::make('Riwayat Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [LoanStatus::Dikembalikan->value, LoanStatus::Ditolak->value])),
        ];
    }
}
