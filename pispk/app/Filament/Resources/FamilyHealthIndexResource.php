<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FamilyHealthIndexResource\Pages;
use App\Models\FamilyHealthIndex;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FamilyHealthIndexResource extends Resource
{
    protected static ?string $model = FamilyHealthIndex::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Kesehatan Keluarga';

    protected static ?string $navigationLabel = 'Indeks Keluarga Sehat';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('family.family_number')
                    ->label('Nomor KK')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('family.head_name')
                    ->label('Kepala Keluarga')
                    ->searchable(),

                Tables\Columns\TextColumn::make('family.building.village.name')
                    ->label('Desa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('iks_value')
                    ->label('Nilai IKS')
                    ->formatStateUsing(fn(float $state): string => number_format($state * 100, 2) . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('health_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Keluarga Sehat' => 'success',
                        'Keluarga Pra-Sehat' => 'warning',
                        'Keluarga Tidak Sehat' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('relevant_indicators')
                    ->label('Indikator Relevan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fulfilled_indicators')
                    ->label('Indikator Terpenuhi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('calculated_at')
                    ->label('Dihitung Pada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('health_status')
                    ->label('Status Kesehatan')
                    ->options([
                        'Keluarga Sehat' => 'Keluarga Sehat',
                        'Keluarga Pra-Sehat' => 'Keluarga Pra-Sehat',
                        'Keluarga Tidak Sehat' => 'Keluarga Tidak Sehat',
                    ]),

                Tables\Filters\Filter::make('iks_value_high')
                    ->label('IKS > 80%')
                    ->query(fn(Builder $query): Builder => $query->where('iks_value', '>', 0.8)),

                Tables\Filters\Filter::make('iks_value_medium')
                    ->label('IKS 50-80%')
                    ->query(fn(Builder $query): Builder => $query
                        ->where('iks_value', '>=', 0.5)
                        ->where('iks_value', '<=', 0.8)),

                Tables\Filters\Filter::make('iks_value_low')
                    ->label('IKS < 50%')
                    ->query(fn(Builder $query): Builder => $query->where('iks_value', '<', 0.5)),

                Tables\Filters\SelectFilter::make('village_id')
                    ->label('Desa')
                    ->relationship('family.building.village', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('recalculate')
                    ->label('Hitung Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (FamilyHealthIndex $record) {
                        $family = $record->family;
                        $iksData = $family->calculateIks();
                        $family->saveIksResult($iksData);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('recalculate_selected')
                    ->label('Hitung Ulang yang Dipilih')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $family = $record->family;
                            $iksData = $family->calculateIks();
                            $family->saveIksResult($iksData);
                        }
                    }),
            ])
            ->defaultSort('calculated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilyHealthIndices::route('/'),
            'view' => Pages\ViewFamilyHealthIndex::route('/{record}'),
        ];
    }
}
