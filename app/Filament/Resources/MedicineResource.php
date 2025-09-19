<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineResource\Pages;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;
    protected static ?string $navigationLabel = 'Obat';
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Manajemen Obat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Obat')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Obat')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('generic_name')
                            ->label('Nama Generik')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('strength')
                            ->label('Kekuatan')
                            ->placeholder('500mg, 250mg, dll')
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('unit')
                            ->label('Satuan')
                            ->options([
                                'tablet' => 'Tablet',
                                'kapsul' => 'Kapsul',
                                'botol' => 'Botol',
                                'tube' => 'Tube',
                                'ampul' => 'Ampul',
                                'vial' => 'Vial',
                                'sachet' => 'Sachet',
                                'strip' => 'Strip',
                            ])
                            ->default('tablet')
                            ->required(),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Manajemen Stok')
                    ->schema([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Jumlah Stok')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->minValue(0),

                        Forms\Components\TextInput::make('stock_initial')
                            ->label('Stok Awal')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Kosongkan untuk menggunakan nilai stok saat ini sebagai stok awal'),

                        Forms\Components\TextInput::make('minimum_stock')
                            ->label('Stok Minimum')
                            ->numeric()
                            ->default(10)
                            ->required()
                            ->minValue(0)
                            ->helperText('Sistem akan memberikan peringatan jika stok mencapai batas ini'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Obat yang tidak aktif tidak akan muncul dalam pilihan resep'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Obat')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('generic_name')
                    ->label('Nama Generik')
                    ->searchable()
                    ->toggleable(),
                
                TextColumn::make('strength')
                    ->label('Kekuatan')
                    ->toggleable(),
                
                TextColumn::make('unit')
                    ->label('Satuan')
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('stock_quantity')
                    ->label('Stok')
                    ->sortable()
                    ->color(fn ($record) => $record->isOutOfStock() ? 'danger' : ($record->isLowStock() ? 'warning' : 'success'))
                    ->weight(fn ($record) => $record->isLowStock() ? 'bold' : 'normal'),

                TextColumn::make('stock_initial')
                    ->label('Stok Awal')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                BadgeColumn::make('stock_status')
                    ->label('Status Stok')
                    ->colors([
                        'danger' => 'Habis',
                        'warning' => 'Stok Menipis',
                        'success' => 'Tersedia',
                    ]),
                
                TextColumn::make('minimum_stock')
                    ->label('Stok Min')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Tidak Aktif'),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Aktif',
                        false => 'Tidak Aktif',
                    ]),
                
                SelectFilter::make('stock_status')
                    ->label('Status Stok')
                    ->options([
                        'low' => 'Stok Menipis',
                        'out' => 'Habis',
                        'available' => 'Tersedia',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === 'low',
                            fn (Builder $query): Builder => $query->lowStock()->where('stock_quantity', '>', 0),
                        )->when(
                            $data['value'] === 'out',
                            fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 0),
                        )->when(
                            $data['value'] === 'available',
                            fn (Builder $query): Builder => $query->whereRaw('stock_quantity > minimum_stock'),
                        );
                    }),
                
                SelectFilter::make('unit')
                    ->label('Satuan')
                    ->options([
                        'tablet' => 'Tablet',
                        'kapsul' => 'Kapsul',
                        'botol' => 'Botol',
                        'tube' => 'Tube',
                        'ampul' => 'Ampul',
                        'vial' => 'Vial',
                        'sachet' => 'Sachet',
                        'strip' => 'Strip',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('adjust_stock')
                    ->label('Sesuaikan Stok')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->form([
                        Forms\Components\TextInput::make('adjustment')
                            ->label('Penyesuaian Stok')
                            ->numeric()
                            ->required()
                            ->helperText('Masukkan angka positif untuk menambah stok, negatif untuk mengurangi'),
                        
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan')
                            ->required()
                            ->placeholder('Contoh: Pembelian baru, expired, rusak, dll'),
                    ])
                    ->action(function (Medicine $record, array $data): void {
                        $newStock = $record->stock_quantity + $data['adjustment'];
                        if ($newStock < 0) {
                            $newStock = 0;
                        }
                        
                        $record->update(['stock_quantity' => $newStock]);
                        
                        // Log the adjustment (you can create a stock_adjustments table later if needed)
                        \Filament\Notifications\Notification::make()
                            ->title('Stok berhasil disesuaikan')
                            ->body("Stok {$record->name} diubah dari {$record->getOriginal('stock_quantity')} menjadi {$newStock}")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Non-aktifkan')
                        ->icon('heroicon-o-x-mark')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicines::route('/'),
            'create' => Pages\CreateMedicine::route('/create'),
            'view' => Pages\ViewMedicine::route('/{record}'),
            'edit' => Pages\EditMedicine::route('/{record}/edit'),
        ];
    }
}
