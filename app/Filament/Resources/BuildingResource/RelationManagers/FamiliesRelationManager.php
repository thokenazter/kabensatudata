<?php

namespace App\Filament\Resources\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FamiliesRelationManager extends RelationManager
{
    protected static string $relationship = 'families';
    protected static ?string $title = 'Keluarga';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Keluarga')
                    ->schema([
                        Forms\Components\TextInput::make('family_number')
                            ->label('No Urut Keluarga')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('head_name')
                            ->label('Nama Kepala Keluarga')
                            ->required()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Informasi Kesehatan Keluarga')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('has_clean_water')
                                    ->label('Tersedia sarana air bersih di lingkungan rumah?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark'),

                                Forms\Components\Toggle::make('is_water_protected')
                                    ->label('Jenis sumber air terlindungi?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->visible(fn(Forms\Get $get) => $get('has_clean_water') === true),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('has_toilet')
                                    ->label('Tersedia jamban keluarga?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark'),

                                Forms\Components\Toggle::make('is_toilet_sanitary')
                                    ->label('Jenis jamban saniter?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->visible(fn(Forms\Get $get) => $get('has_toilet') === true),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('has_mental_illness')
                                    ->label('Ada anggota keluarga yang pernah menderita gangguan jiwa berat?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark'),

                                Forms\Components\Toggle::make('takes_medication_regularly')
                                    ->label('Penderita minum obat teratur?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->visible(fn(Forms\Get $get) => $get('has_mental_illness') === true),
                            ]),

                        Forms\Components\Toggle::make('has_restrained_member')
                            ->label('Ada anggota keluarga yang dipasung?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building.village.name')
                    ->label('Desa')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('building.building_number')
                    ->label('No Bangunan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('family_number')
                    ->label('No Keluarga')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('head_name')
                    ->label('Kepala Keluarga')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('has_clean_water')
                    ->label('Air Bersih')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_toilet')
                    ->label('Jamban')
                    ->boolean(),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('Anggota')
                    ->counts('members'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('building_id')
                    ->label('Bangunan')
                    ->relationship('building', 'building_number'),

                Tables\Filters\SelectFilter::make('village')
                    ->label('Desa')
                    ->relationship('building.village', 'name'),

                Tables\Filters\TernaryFilter::make('has_clean_water')
                    ->label('Memiliki Air Bersih'),

                Tables\Filters\TernaryFilter::make('has_toilet')
                    ->label('Memiliki Jamban'),

                Tables\Filters\TernaryFilter::make('has_mental_illness')
                    ->label('Ada Penderita Gangguan Jiwa'),

                Tables\Filters\TernaryFilter::make('has_restrained_member')
                    ->label('Ada Anggota Dipasung'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilies::route('/'),
            'create' => Pages\CreateFamily::route('/create'),
            'edit' => Pages\EditFamily::route('/{record}/edit'),
        ];
    }
}
