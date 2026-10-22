<?php

namespace App\Filament\Resources\EquipmentResource\RelationManagers;

use App\Enums\UnitStatus;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $title = 'Daftar Unit Fisik';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_code')
                    ->label('Kode Unit')
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'units', column: 'unit_code', ignoreRecord: true),
                Forms\Components\Select::make('status')
                    ->label('Status Unit')
                    ->options(collect(UnitStatus::cases())->mapWithKeys(fn (UnitStatus $status) => [$status->value => $status->label()]))
                    ->default(UnitStatus::Tersedia->value)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('unit_code')
            ->columns([
                Tables\Columns\TextColumn::make('unit_code')
                    ->label('Kode Unit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (UnitStatus $state): string => $state->label())
                    ->color(fn (UnitStatus $state): string => match ($state) {
                        UnitStatus::Tersedia => 'success',
                        UnitStatus::Ditahan => 'warning',
                        UnitStatus::Dipinjam => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(UnitStatus::cases())->mapWithKeys(fn (UnitStatus $status) => [$status->value => $status->label()])),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Unit $record): void {
                        if ($record->hasActiveOrPendingLoans()) {
                            Notification::make()
                                ->title('Penghapusan Ditolak')
                                ->body('Unit tidak dapat dihapus karena masih memiliki pinjaman berstatus menunggu atau aktif.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    }),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
