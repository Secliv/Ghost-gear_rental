<?php

namespace App\Filament\Resources;

use App\Enums\LoanStatus;
use App\Enums\UnitStatus;
use App\Enums\UserRole;
use App\Filament\Resources\LoanResource\Pages;
use App\Models\Loan;
use App\Models\Unit;
use App\Models\User;
use App\Services\LoanService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Manajemen Peminjaman';

    protected static ?string $modelLabel = 'Peminjaman';

    protected static ?string $pluralModelLabel = 'Peminjaman Peralatan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Peminjaman')
                    ->schema([
                        Forms\Components\Select::make('member_id')
                            ->label('Anggota')
                            ->options(
                                User::query()
                                    ->where('role', UserRole::Anggota->value)
                                    ->with('profile')
                                    ->get()
                                    ->mapWithKeys(fn (User $user) => [$user->id => ($user->profile?->full_name ?? $user->username)." ({$user->username})"])
                            )
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('unit_id')
                            ->label('Unit Alat')
                            ->options(
                                Unit::query()
                                    ->where('status', UnitStatus::Tersedia->value)
                                    ->with('equipment')
                                    ->get()
                                    ->mapWithKeys(fn (Unit $unit) => [$unit->id => "{$unit->unit_code} - {$unit->equipment->name}"])
                            )
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.profile.full_name')
                    ->label('Anggota')
                    ->description(fn (Loan $record): string => "@{$record->member?->username}")
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit.unit_code')
                    ->label('Kode Unit')
                    ->description(fn (Loan $record): string => $record->unit?->equipment?->name ?? '-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (LoanStatus $state): string => $state->label())
                    ->color(fn (Loan $record): string => match ($record->status) {
                        LoanStatus::Menunggu => 'warning',
                        LoanStatus::Aktif => $record->isLate() ? 'danger' : 'info',
                        LoanStatus::Ditolak => 'gray',
                        LoanStatus::Dikembalikan => 'success',
                    })
                    ->description(fn (Loan $record): ?string => ($record->status === LoanStatus::Aktif && $record->isLate()) ? 'TERLAMBAT' : null),

                Tables\Columns\TextColumn::make('requested_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('borrow_date')
                    ->label('Pinjam')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Tenggat')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->color(fn (Loan $record): ?string => ($record->status === LoanStatus::Aktif && $record->isLate()) ? 'danger' : null)
                    ->sortable(),

                Tables\Columns\TextColumn::make('return_date')
                    ->label('Kembali')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('late_days')
                    ->label('Telat')
                    ->formatStateUsing(fn (int $state): string => "{$state} hari")
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('fine_amount')
                    ->label('Denda')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((float) $state, 0, ',', '.'))
                    ->description(fn (Loan $record): ?string => $record->fine_amount > 0 ? ($record->fine_paid ? 'LUNAS' : 'BELUM LUNAS') : null)
                    ->color(fn (Loan $record): ?string => $record->fine_amount > 0 ? ($record->fine_paid ? 'success' : 'danger') : null)
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Pinjaman')
                    ->options(collect(LoanStatus::cases())->mapWithKeys(fn (LoanStatus $status) => [$status->value => $status->label()])),

                Tables\Filters\SelectFilter::make('member_id')
                    ->label('Anggota')
                    ->options(
                        User::query()
                            ->where('role', UserRole::Anggota->value)
                            ->with('profile')
                            ->get()
                            ->mapWithKeys(fn (User $user) => [$user->id => $user->profile?->full_name ?? $user->username])
                    )
                    ->searchable(),

                Tables\Filters\Filter::make('requested_between')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('requested_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('requested_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Loan $record): bool => $record->status === LoanStatus::Menunggu)
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pengajuan Pinjaman')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui peminjaman ini? Unit akan otomatis berstatus dipinjam.')
                    ->action(function (Loan $record, LoanService $loanService): void {
                        try {
                            $loanService->approve($record, auth()->user());
                            Notification::make()
                                ->title('Peminjaman Disetujui')
                                ->body("Tenggat pengembalian: {$record->fresh()->due_date?->format('d M Y')}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Menyetujui')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Loan $record): bool => $record->status === LoanStatus::Menunggu)
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pengajuan Pinjaman')
                    ->modalDescription('Apakah Anda yakin ingin menolak pengajuan ini? Unit akan kembali berstatus tersedia.')
                    ->action(function (Loan $record, LoanService $loanService): void {
                        try {
                            $loanService->reject($record, auth()->user());
                            Notification::make()
                                ->title('Pengajuan Ditolak')
                                ->body('Unit telah dikembalikan ke status tersedia.')
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Menolak')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('returnLoan')
                    ->label('Catat Pengembalian')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('primary')
                    ->visible(fn (Loan $record): bool => $record->status === LoanStatus::Aktif)
                    ->form([
                        Forms\Components\DatePicker::make('return_date')
                            ->label('Tanggal Pengembalian Fisik')
                            ->default(Carbon::now())
                            ->required(),
                    ])
                    ->action(function (Loan $record, array $data, LoanService $loanService): void {
                        try {
                            $returnDate = Carbon::parse($data['return_date']);
                            $returnedLoan = $loanService->returnLoan($record, auth()->user(), $returnDate);

                            $msg = 'Pengembalian berhasil dicatat. ';
                            if ($returnedLoan->late_days > 0) {
                                $msg .= "Terlambat {$returnedLoan->late_days} hari. Total denda: Rp ".number_format($returnedLoan->fine_amount, 0, ',', '.');
                            } else {
                                $msg .= 'Tepat waktu tanpa denda.';
                            }

                            Notification::make()
                                ->title('Unit Dikembalikan')
                                ->body($msg)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Mengembalikan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('markFinePaid')
                    ->label('Tandai Denda Lunas')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Loan $record): bool => $record->fine_amount > 0 && ! $record->fine_paid)
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembayaran Denda')
                    ->modalDescription('Tandai bahwa denda sebesar Rp '.number_format(1000, 0).' telah dibayar di luar sistem?')
                    ->action(function (Loan $record, LoanService $loanService): void {
                        try {
                            $loanService->markFinePaid($record, auth()->user());
                            Notification::make()
                                ->title('Denda Lunas')
                                ->body('Status denda berhasil ditandai telah dibayar.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Memperbarui Denda')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
        ];
    }
}
