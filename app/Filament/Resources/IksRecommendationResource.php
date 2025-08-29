<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IksRecommendationResource\Pages;
use App\Filament\Resources\IksRecommendationResource\RelationManagers;
use App\Models\IksRecommendation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IksRecommendationResource extends Resource
{
    protected static ?string $model = IksRecommendation::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationGroup = 'Kesehatan Keluarga';

    protected static ?string $navigationLabel = 'Rekomendasi IKS';

    protected static ?int $navigationSort = 6;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNotIn('status', ['completed', 'rejected'])->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('priority_level', 'High')
            ->whereNotIn('status', ['completed', 'rejected'])
            ->exists() ? 'danger' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Rekomendasi')
                    ->schema([
                        Forms\Components\Select::make('family_id')
                            ->relationship('family', 'head_name')
                            ->label('Keluarga')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Rekomendasi')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('indicator_code')
                            ->label('Indikator')
                            ->options([
                                'kb' => 'Keluarga Berencana',
                                'birth_facility' => 'Persalinan di Fasilitas Kesehatan',
                                'immunization' => 'Imunisasi Dasar Lengkap',
                                'exclusive_breastfeeding' => 'ASI Eksklusif',
                                'growth_monitoring' => 'Pemantauan Pertumbuhan',
                                'tb_treatment' => 'Pengobatan TB',
                                'hypertension_treatment' => 'Pengobatan Hipertensi',
                                'mental_treatment' => 'Pengobatan Gangguan Jiwa',
                                'no_smoking' => 'Tidak Merokok',
                                'jkn_membership' => 'Kepesertaan JKN',
                                'clean_water' => 'Akses Air Bersih',
                                'sanitary_toilet' => 'Jamban Sehat',
                            ])
                            ->required(),

                        Forms\Components\Select::make('recommendation_type')
                            ->label('Jenis Rekomendasi')
                            ->options([
                                'education' => 'Edukasi',
                                'intervention' => 'Intervensi',
                                'treatment' => 'Pengobatan',
                                'monitoring' => 'Pemantauan',
                                'lifestyle' => 'Gaya Hidup',
                                'administration' => 'Administrasi',
                                'infrastructure' => 'Infrastruktur',
                                'general' => 'Umum',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->maxLength(1000),
                    ]),

                Forms\Components\Section::make('Detail Rekomendasi')
                    ->schema([
                        Forms\Components\Select::make('priority_level')
                            ->label('Level Prioritas')
                            ->options([
                                'High' => 'Tinggi',
                                'Medium' => 'Sedang',
                                'Low' => 'Rendah',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('priority_score')
                            ->label('Skor Prioritas')
                            ->numeric()
                            ->required(),

                        Forms\Components\Select::make('difficulty_level')
                            ->label('Tingkat Kesulitan')
                            ->options([
                                'Easy' => 'Mudah',
                                'Medium' => 'Sedang',
                                'Hard' => 'Sulit',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('expected_days_to_complete')
                            ->label('Estimasi Waktu Penyelesaian (hari)')
                            ->numeric()
                            ->required(),

                        Forms\Components\DatePicker::make('target_date')
                            ->label('Tanggal Target')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Langkah Tindakan')
                    ->schema([
                        Forms\Components\Repeater::make('actions')
                            ->label('Tindakan yang Diperlukan')
                            ->schema([
                                Forms\Components\TextInput::make('action')
                                    ->label('Tindakan')
                                    ->required(),
                            ])
                            ->columns(1)
                            ->required()
                            ->minItems(1),

                        Forms\Components\Repeater::make('resources')
                            ->label('Sumber Daya yang Diperlukan')
                            ->schema([
                                Forms\Components\TextInput::make('resource')
                                    ->label('Sumber Daya')
                                    ->required(),
                            ])
                            ->columns(1)
                            ->required()
                            ->minItems(1),
                    ]),

                Forms\Components\Section::make('Status dan Catatan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu',
                                'in_progress' => 'Dalam Proses',
                                'completed' => 'Selesai',
                                'rejected' => 'Ditolak',
                            ])
                            ->required(),

                        Forms\Components\DatePicker::make('completed_date')
                            ->label('Tanggal Penyelesaian')
                            ->visible(fn($get) => $get('status') === 'completed'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000),
                    ]),
            ]);
    }

    /**
     * Helper untuk mendapatkan nama indikator dari kode
     */
    public static function getIndicatorName(string $code): string
    {
        $names = [
            'kb' => 'Keluarga Berencana',
            'birth_facility' => 'Persalinan di Fasilitas Kesehatan',
            'immunization' => 'Imunisasi Dasar Lengkap',
            'exclusive_breastfeeding' => 'ASI Eksklusif',
            'growth_monitoring' => 'Pemantauan Pertumbuhan',
            'tb_treatment' => 'Pengobatan TB',
            'hypertension_treatment' => 'Pengobatan Hipertensi',
            'mental_treatment' => 'Pengobatan Gangguan Jiwa',
            'no_smoking' => 'Tidak Merokok',
            'jkn_membership' => 'Kepesertaan JKN',
            'clean_water' => 'Akses Air Bersih',
            'sanitary_toilet' => 'Jamban Sehat',
        ];

        return $names[$code] ?? 'Indikator Tidak Dikenal';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('family.head_name')
                    ->label('Keluarga')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('indicator_code')
                    ->label('Indikator')
                    ->formatStateUsing(fn($state) => static::getIndicatorName($state))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Rekomendasi')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('priority_level')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'High' => 'danger',
                        'Medium' => 'warning',
                        'Low' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'warning',
                        'pending' => 'gray',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('target_date')
                    ->label('Target')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('indicator_code')
                    ->label('Indikator')
                    ->options([
                        'kb' => 'Keluarga Berencana',
                        'birth_facility' => 'Persalinan di Fasilitas Kesehatan',
                        'immunization' => 'Imunisasi Dasar Lengkap',
                        'exclusive_breastfeeding' => 'ASI Eksklusif',
                        'growth_monitoring' => 'Pemantauan Pertumbuhan',
                        'tb_treatment' => 'Pengobatan TB',
                        'hypertension_treatment' => 'Pengobatan Hipertensi',
                        'mental_treatment' => 'Pengobatan Gangguan Jiwa',
                        'no_smoking' => 'Tidak Merokok',
                        'jkn_membership' => 'Kepesertaan JKN',
                        'clean_water' => 'Akses Air Bersih',
                        'sanitary_toilet' => 'Jamban Sehat',
                    ]),

                Tables\Filters\SelectFilter::make('priority_level')
                    ->label('Prioritas')
                    ->options([
                        'High' => 'Tinggi',
                        'Medium' => 'Sedang',
                        'Low' => 'Rendah',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_progress' => 'Dalam Proses',
                        'completed' => 'Selesai',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\Filter::make('overdue')
                    ->label('Lewat Batas Waktu')
                    ->query(fn(Builder $query): Builder => $query
                        ->where('status', '!=', 'completed')
                        ->where('status', '!=', 'rejected')
                        ->whereDate('target_date', '<', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_in_progress')
                    ->label('Dalam Proses')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->action(fn(IksRecommendation $record) => $record->markAsInProgress())
                    ->visible(fn(IksRecommendation $record) => $record->status === 'pending'),

                Tables\Actions\Action::make('mark_completed')
                    ->label('Selesai')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn(IksRecommendation $record) => $record->markAsCompleted())
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Penyelesaian')
                            ->placeholder('Tambahkan catatan tentang penyelesaian rekomendasi ini')
                            ->maxLength(500),
                    ])
                    ->visible(fn(IksRecommendation $record) => in_array($record->status, ['pending', 'in_progress'])),

                Tables\Actions\Action::make('mark_rejected')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn(IksRecommendation $record, array $data) => $record->markAsRejected($data['notes'] ?? null))
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->placeholder('Tambahkan alasan penolakan rekomendasi ini')
                            ->maxLength(500),
                    ])
                    ->visible(fn(IksRecommendation $record) => in_array($record->status, ['pending', 'in_progress'])),

                Tables\Actions\Action::make('print_recommendation')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->url(fn(IksRecommendation $record) => route('recommendations.print', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('mark_multiple_in_progress')
                        ->label('Tandai Dalam Proses')
                        ->icon('heroicon-o-play')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->markAsInProgress();
                                }
                            }
                        }),

                    Tables\Actions\BulkAction::make('mark_multiple_completed')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                if (in_array($record->status, ['pending', 'in_progress'])) {
                                    $record->markAsCompleted($data['notes'] ?? null);
                                }
                            }
                        })
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan Penyelesaian')
                                ->placeholder('Tambahkan catatan penyelesaian untuk semua rekomendasi yang dipilih')
                                ->maxLength(500),
                        ]),
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
            'index' => Pages\ListIksRecommendations::route('/'),
            'create' => Pages\CreateIksRecommendation::route('/create'),
            'view' => Pages\ViewIksRecommendation::route('/{record}'),
            'edit' => Pages\EditIksRecommendation::route('/{record}/edit'),
        ];
    }
}
