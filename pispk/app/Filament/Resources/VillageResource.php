<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VillageResource\Pages;
use App\Filament\Resources\VillageResource\RelationManagers;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VillageResource extends Resource
{
    protected static ?string $model = Village::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Desa';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Desa')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('code')
                    ->label('Kode Desa')
                    ->maxLength(50),

                Forms\Components\TextInput::make('district')
                    ->label('Kecamatan')
                    ->maxLength(255),

                Forms\Components\TextInput::make('regency')
                    ->label('Kabupaten')
                    ->maxLength(255),

                Forms\Components\TextInput::make('province')
                    ->label('Provinsi')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Desa')
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Desa')
                    ->searchable(),

                Tables\Columns\TextColumn::make('district')
                    ->label('Kecamatan'),

                Tables\Columns\TextColumn::make('regency')
                    ->label('Kabupaten'),

                Tables\Columns\TextColumn::make('province')
                    ->label('Provinsi'),

                Tables\Columns\TextColumn::make('buildings_count')
                    ->label('Jumlah Bangunan')
                    ->counts('buildings'),
            ])
            ->filters([
                //
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
            RelationManagers\BuildingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVillages::route('/'),
            'create' => Pages\CreateVillage::route('/create'),
            'edit' => Pages\EditVillage::route('/{record}/edit'),
        ];
    }
}
