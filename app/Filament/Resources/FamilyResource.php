<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Family;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Services\IksService;
use Filament\Resources\Resource;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use App\Filament\Resources\FamilyResource\Pages;
use App\Filament\Resources\FamilyResource\RelationManagers;


class FamilyResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Keluarga';
    protected static ?int $navigationSort = 3;
    protected static ?string $model = Family::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Keluarga')
                    ->schema([
                        Forms\Components\Select::make('building_id')
                            ->label('Bangunan')
                            ->relationship('building', 'building_number', function ($query) {
                                return $query->with('village');
                            })
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->village->name} - No. {$record->building_number}")
                            ->searchable()
                            ->preload()
                            ->required(),

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
                                Toggle::make('has_clean_water')
                                    ->label('3. Tersedia sarana air bersih di lingkungan rumah?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->reactive(),  // Pastikan form bisa bereaksi terhadap perubahan nilai

                                Toggle::make('is_water_protected')
                                    ->label('4. Jenis sumber air terlindungi?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->visible(fn(Forms\Get $get) => $get('has_clean_water') === true),  // Tetap dikontrol visibilitasnya
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('has_toilet')
                                    ->label('5. Tersedia jamban keluarga?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->reactive(),

                                Forms\Components\Toggle::make('is_toilet_sanitary')
                                    ->label('6. Jenis jamban saniter?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->visible(fn(Forms\Get $get) => $get('has_toilet') === true),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('has_mental_illness')
                                    ->label('7. Ada anggota keluarga yang pernah menderita gangguan jiwa berat?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->reactive(),
                                Forms\Components\Toggle::make('takes_medication_regularly')
                                    ->label('8. Apakah selama ini penderita minum obat gangguan jiwa secara teratur?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->reactive()
                                    ->visible(fn(Forms\Get $get) => $get('has_mental_illness') === true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
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

                // Tambahkan kolom IKS
                Tables\Columns\TextColumn::make('healthIndex.iks_value')
                    ->label('IKS')
                    ->formatStateUsing(function ($state) {
                        return $state ? number_format($state * 100, 2) . '%' : '-';
                    })
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('iks_change')
                    ->label('Perubahan IKS')
                    ->formatStateUsing(function ($state, $record) {
                        if ($state == 0 || is_null($state)) {
                            return '-';
                        }

                        $arrow = $state > 0 ? '↑' : '↓';
                        $color = $state > 0 ? 'text-green-600' : 'text-red-600';

                        return "<span class='{$color} font-medium'>{$arrow} " . abs(number_format($state * 100, 2)) . "%</span>";
                    })
                    ->html()
                    ->alignCenter(),
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
                Tables\Actions\Action::make('view_history')
                    ->label('Riwayat IKS')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->url(fn(Family $record) => route('families.history', $record))
                    ->visible(fn(Family $record) => $record->healthIndexHistories()->count() > 0),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('calculate_iks')
                    ->label('Hitung IKS')
                    ->icon('heroicon-o-heart')
                    ->color('success')
                    ->action(function (Family $record, array $data) {
                        $notes = $data['notes'] ?? null;

                        $iksService = app(IksService::class);
                        $iksData = $iksService->calculateIks($record, $notes);

                        // Simpan ke dalam tabel utama
                        $record->saveIksResult($iksData);

                        // Simpan juga sebagai riwayat
                        $record->saveIksHistory($iksData);

                        $changeText = '';
                        if (isset($iksData['net_change'])) {
                            $change = $iksData['net_change'] * 100;
                            $changeText = $change > 0
                                ? " (Meningkat " . number_format(abs($change), 2) . "%)"
                                : " (Menurun " . number_format(abs($change), 2) . "%)";
                        }

                        Notification::make()
                            ->title('IKS berhasil dihitung')
                            ->body("Nilai IKS: " . number_format($iksData['iks_value'] * 100, 2) . "% (" . $iksData['health_status'] . "){$changeText}")
                            ->success()
                            ->send();
                    })
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Perhitungan')
                            ->placeholder('Tambahkan catatan tentang perhitungan IKS ini')
                            ->maxLength(1000)
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Hitung Indeks Keluarga Sehat')
                    ->modalDescription('Apakah Anda yakin ingin menghitung Indeks Keluarga Sehat (IKS) untuk keluarga ini?')
                    ->modalSubmitActionLabel('Ya, Hitung IKS'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // Tambahkan bulk action Hitung IKS
                    Tables\Actions\BulkAction::make('calculate_iks_bulk')
                        ->label('Hitung IKS')
                        ->icon('heroicon-o-heart')
                        ->action(function ($records) {
                            $count = 0;
                            $successCount = 0;

                            foreach ($records as $record) {
                                try {
                                    $iksService = app(IksService::class);
                                    $iksData = $iksService->calculateIks($record);
                                    $record->saveIksResult($iksData);
                                    $record->saveIksHistory($iksData);
                                    $successCount++;
                                } catch (\Exception $e) {
                                    // Log error jika diperlukan
                                }
                                $count++;
                            }

                            Notification::make()
                                ->title('IKS berhasil dihitung')
                                ->body("Berhasil menghitung IKS untuk {$successCount} dari {$count} keluarga.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Hitung Indeks Keluarga Sehat (Batch)')
                        ->modalDescription('Apakah Anda yakin ingin menghitung Indeks Keluarga Sehat (IKS) untuk keluarga yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hitung IKS')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
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
            'index' => Pages\ListFamilies::route('/'),
            'create' => Pages\CreateFamily::route('/create'),
            'edit' => Pages\EditFamily::route('/{record}/edit'),
        ];
    }
}
