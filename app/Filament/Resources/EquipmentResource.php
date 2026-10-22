<?php

namespace App\Filament\Resources;

use App\Enums\DeviceType;
use App\Filament\Resources\EquipmentResource\Pages;
use App\Filament\Resources\EquipmentResource\RelationManagers\UnitsRelationManager;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Peralatan Investigasi';

    protected static ?string $modelLabel = 'Alat';

    protected static ?string $pluralModelLabel = 'Peralatan Investigasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar Alat')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Alat')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('device_type')
                            ->label('Jenis Perangkat')
                            ->options(collect(DeviceType::cases())->mapWithKeys(fn (DeviceType $type) => [$type->value => $type->label()]))
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('categories')
                            ->label('Kategori')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Spesifikasi Teknis Khusus')
                    ->description('Field spesifikasi akan menyesuaikan dengan jenis perangkat yang dipilih.')
                    ->schema([
                        Forms\Components\TextInput::make('emf_range')
                            ->label('Rentang EMF')
                            ->placeholder('Contoh: 50-60 Hz (1.5 - 20 mG)')
                            ->visible(fn (Get $get): bool => $get('device_type') === DeviceType::EmfMeter->value),

                        Forms\Components\TextInput::make('thermal_temp_range_c')
                            ->label('Rentang Suhu Termal (°C)')
                            ->placeholder('Contoh: -20°C sampai 250°C')
                            ->visible(fn (Get $get): bool => $get('device_type') === DeviceType::ThermalCamera->value),

                        Forms\Components\TextInput::make('night_vision_resolution')
                            ->label('Resolusi Night Vision')
                            ->placeholder('Contoh: 1080p 60fps Full Spectrum')
                            ->visible(fn (Get $get): bool => $get('device_type') === DeviceType::NightVisionCamera->value),

                        Forms\Components\TextInput::make('audio_record_hours')
                            ->label('Kapasitas Rekam Audio (Jam)')
                            ->numeric()
                            ->step(0.1)
                            ->visible(fn (Get $get): bool => $get('device_type') === DeviceType::EvpRecorder->value),

                        Forms\Components\TextInput::make('motion_sensor_type')
                            ->label('Tipe Sensor Gerak')
                            ->placeholder('Contoh: PIR Inframerah Pasif & Getaran 3-Axis')
                            ->visible(fn (Get $get): bool => $get('device_type') === DeviceType::MotionSensor->value),

                        Forms\Components\TextInput::make('battery_hours')
                            ->label('Daya Tahan Baterai (Jam)')
                            ->numeric()
                            ->step(0.1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Alat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('device_type')
                    ->label('Jenis Perangkat')
                    ->badge()
                    ->formatStateUsing(fn (DeviceType $state): string => $state->label())
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Kategori')
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('units_count')
                    ->label('Total Unit')
                    ->counts('units'),
                Tables\Columns\TextColumn::make('available_units_count')
                    ->label('Unit Tersedia')
                    ->counts('availableUnits')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('device_type')
                    ->label('Jenis Perangkat')
                    ->options(collect(DeviceType::cases())->mapWithKeys(fn (DeviceType $type) => [$type->value => $type->label()])),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
