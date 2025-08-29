<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Building;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use Filament\Resources\Resource;
use Illuminate\Validation\Rules\Unique;
use App\Filament\Resources\BuildingResource\Pages;
use App\Filament\Resources\BuildingResource\RelationManagers;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Bangunan';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('village_id')
                    ->label('Desa')
                    ->relationship('village', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live() // Penting: ini akan memicu pembaruan validasi saat desa dipilih
                    ->afterStateUpdated(fn(callable $set) => $set('building_number', null)), // Reset building_number

                Forms\Components\TextInput::make('building_number')
                    ->label('No Urut Bangunan')
                    ->required()
                    ->maxLength(255)
                    ->rules([
                        function ($get) {
                            return (new Unique('buildings', 'building_number'))
                                ->where('village_id', $get('village_id'))
                                ->ignore($get('id'));
                        }
                    ])
                    ->validationMessages([
                        'unique' => 'No Urut Bangunan Tersebut Sudah di Gunakan Pada Desa ini',
                    ])
                    ->helperText('Nomor urut bangunan harus unik untuk desa yang sama'),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude'),

                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude'),
                    ]),

                Forms\Components\Textarea::make('description')
                    ->label('Keterangan')
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('village.name')
                    ->label('Desa')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('building_number')
                    ->label('No Urut Bangunan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('families_count')
                    ->label('Jumlah Keluarga')
                    ->counts('families'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('village_id')
                    ->label('Desa')
                    ->relationship('village', 'name'),
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
            RelationManagers\FamiliesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}
