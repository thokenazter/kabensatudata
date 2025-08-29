<?php

namespace App\Filament\Resources\VillageResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BuildingsRelationManager extends RelationManager
{
    protected static string $relationship = 'buildings';
    protected static ?string $title = 'Bangunan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('building_number')
                    ->label('No Urut Bangunan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('longitude')
                            ->label('longitude'),

                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude'),
                    ]),

                Forms\Components\Textarea::make('description')
                    ->label('Keterangan')
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building_number')
                    ->label('No Urut Bangunan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('longitude')
                    ->label('Longitude'),

                Tables\Columns\TextColumn::make('latitude')
                    ->label('Latitude'),

                Tables\Columns\TextColumn::make('families_count')
                    ->label('Jumlah Keluarga')
                    ->counts('families'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
