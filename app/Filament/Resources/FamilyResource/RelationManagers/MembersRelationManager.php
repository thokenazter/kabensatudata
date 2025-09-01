<?php

// app/Filament/Resources/FamilyResource/RelationManagers/MembersRelationManager.php
namespace App\Filament\Resources\FamilyResource\RelationManagers;

use App\Helpers\FamilyMemberFormHelper;
use App\Models\FamilyMember;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';
    protected static ?string $title = 'Anggota Keluarga';

    // Variabel untuk menyimpan record yang sedang diedit
    protected ?FamilyMember $currentRecord = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('relationship')
                            ->label('Hubungan dengan Kepala Keluarga')
                            ->options([
                                'Kepala Keluarga' => 'Kepala Keluarga',
                                'Istri' => 'Istri',
                                'Suami' => 'Suami',
                                'Anak' => 'Anak',
                                'Menantu' => 'Menantu',
                                'Cucu' => 'Cucu',
                                'Orang Tua' => 'Orang Tua',
                                'Mertua' => 'Mertua',
                                'Pembantu' => 'Pembantu',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('birth_place')
                                    ->label('Tempat Lahir')
                                    ->maxLength(255),

                                Forms\Components\DatePicker::make('birth_date')
                                    ->label('Tanggal Lahir')
                                    ->required()
                                    ->maxDate(now())
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $age = Carbon::parse($state)->age;
                                            $set('age', $age);
                                        }
                                    }),
                            ]),

                        Forms\Components\TextInput::make('age')
                            ->label('Umur (Tahun)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('religion')
                            ->label('Agama')
                            ->options([
                                'Islam' => 'Islam',
                                'Kristen' => 'Kristen',
                                'Katolik' => 'Katolik',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Konghucu' => 'Konghucu',
                                'Lainnya' => 'Lainnya',
                            ]),
                    ]),

                Forms\Components\Section::make('Pertanyaan Khusus Perempuan')
                    ->schema([
                        Forms\Components\Toggle::make('is_pregnant')
                            ->label('Sedang Hamil?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function (Get $get) {
                                $birthDate = $get('birth_date');
                                $gender = $get('gender');

                                if (!$birthDate || $gender !== 'Perempuan') {
                                    return false;
                                }

                                $age = Carbon::parse($birthDate)->age;
                                return $age >= 10 && $age <= 54;
                            })
                            ->live(),
                    ])
                    ->visible(fn(Get $get) => $get('gender') === 'Perempuan'),

                Forms\Components\Section::make('Informasi Umum')
                    ->schema([
                        Forms\Components\TextInput::make('occupation')
                            ->label('Pekerjaan')
                            ->maxLength(255)
                            ->visible(function (Get $get) {
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                $age = Carbon::parse($birthDate)->age;
                                return $age > 10;
                            }),

                        Forms\Components\Toggle::make('has_jkn')
                            ->label('Memiliki kartu JKN?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark'),
                    ]),

                Forms\Components\Section::make('Informasi Kesehatan')
                    ->schema([
                        Forms\Components\Toggle::make('is_smoker')
                            ->label('Apakah merokok?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function (Get $get) {
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                $age = Carbon::parse($birthDate)->age;
                                return $age > 15;
                            }),

                        Forms\Components\Toggle::make('use_toilet')
                            ->label('Apakah buang air besar di jamban?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function (Get $get) {
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                $age = Carbon::parse($birthDate)->age;
                                return $age > 15;
                            }),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('has_tuberculosis')
                                    ->label('Pernah didiagnosis TBC Paru?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->live(),

                                Forms\Components\Toggle::make('takes_tb_medication_regularly')
                                    ->label('Minum obat TBC secara teratur?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->visible(fn(Get $get) => $get('has_tuberculosis') === true),
                            ]),

                        Forms\Components\Toggle::make('has_chronic_cough')
                            ->label('Pernah menderita batuk berdahak lebih dari 2 minggu?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('has_hypertension')
                                    ->label('Didiagnosis tekanan darah tinggi?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->visible(function (Get $get) {
                                        $birthDate = $get('birth_date');
                                        if (!$birthDate) {
                                            return false;
                                        }

                                        $age = Carbon::parse($birthDate)->age;
                                        return $age > 15;
                                    })
                                    ->live(),

                                Forms\Components\Toggle::make('takes_hypertension_medication_regularly')
                                    ->label('Minum obat darah tinggi secara teratur?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->visible(fn(Get $get) => $get('has_hypertension') === true),
                            ]),
                    ]),

                Forms\Components\Section::make('Informasi KB dan Kesehatan Reproduksi')
                    ->schema([
                        Forms\Components\Toggle::make('uses_contraception')
                            ->label('Menggunakan alat kontrasepsi atau ikut program KB?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function ($get, $record) {
                                // Cek kriteria KB berdasarkan record yang ada
                                if ($record) {
                                    return FamilyMemberFormHelper::isEligibleForKB($record);
                                }

                                // Untuk record baru, cek berdasarkan data form
                                $gender = $get('gender');
                                $birthDate = $get('birth_date');
                                $isPregnant = $get('is_pregnant');
                                $relationship = $get('relationship');

                                if (!$birthDate) {
                                    return false;
                                }

                                $age = Carbon::parse($birthDate)->age;

                                // Kriteria untuk perempuan
                                if ($gender === 'Perempuan') {
                                    return $age >= 10 && $age <= 54 && !$isPregnant &&
                                        in_array($relationship, ['Kepala Keluarga', 'Istri']);
                                }

                                // Kriteria untuk laki-laki
                                if ($gender === 'Laki-laki') {
                                    return $age > 10 && in_array($relationship, ['Kepala Keluarga', 'Suami']);
                                }

                                return false;
                            }),

                        Forms\Components\Toggle::make('gave_birth_in_health_facility')
                            ->label('Melahirkan di fasilitas kesehatan?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function ($get, $record) {
                                // Untuk ibu yang memiliki anak berusia < 12 bulan
                                if ($record) {
                                    return $record->gender === 'Perempuan' &&
                                        in_array($record->relationship, ['Kepala Keluarga', 'Istri']) &&
                                        FamilyMemberFormHelper::hasInfantUnder12Months($record);
                                }

                                // Untuk form pembuatan, kita perlu mengetahui apakah ada bayi di keluarga ini
                                // Ini hanya bisa ditentukan setelah menyimpan
                                return false;
                            }),

                        Forms\Components\Toggle::make('exclusive_breastfeeding')
                            ->label('Bayi diberi ASI eksklusif 0-6 bulan?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function ($get, $record) {
                                // Untuk anak berusia 7-23 bulan
                                if ($record) {
                                    return FamilyMemberFormHelper::isAgedBetween7And23Months($record);
                                }

                                // Untuk record baru, gunakan data dari form
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                $ageInMonths = Carbon::parse($birthDate)->diffInMonths(Carbon::now());
                                return $ageInMonths >= 7 && $ageInMonths <= 23;
                            }),

                        Forms\Components\Toggle::make('complete_immunization')
                            ->label('Imunisasi lengkap 0-11 bulan?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function ($get, $record) {
                                // Untuk anak berusia 12-23 bulan
                                if ($record) {
                                    return FamilyMemberFormHelper::isAgedBetween12And23Months($record);
                                }

                                // Untuk record baru, gunakan data dari form
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                $ageInMonths = Carbon::parse($birthDate)->diffInMonths(Carbon::now());
                                return $ageInMonths >= 12 && $ageInMonths <= 23;
                            }),

                        Forms\Components\Toggle::make('growth_monitoring')
                            ->label('Pemantauan pertumbuhan balita dalam 1 bulan terakhir?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function ($get, $record) {
                                // Untuk anak berusia 2-59 bulan
                                if ($record) {
                                    return FamilyMemberFormHelper::isAgedBetween2And59Months($record);
                                }

                                // Untuk record baru, gunakan data dari form
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                $ageInMonths = Carbon::parse($birthDate)->diffInMonths(Carbon::now());
                                return $ageInMonths >= 2 && $ageInMonths <= 59;
                            }),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('relationship')
                    ->label('Hubungan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin'),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->date('d-m-Y'),

                Tables\Columns\TextColumn::make('age')
                    ->label('Umur')
                    ->state(function ($record) {
                        if ($record->birth_date) {
                            return Carbon::parse($record->birth_date)->age . ' tahun';
                        }
                        return '-';
                    }),

                Tables\Columns\IconColumn::make('is_pregnant')
                    ->label('Hamil')
                    ->boolean()
                    ->visible(function ($livewire) {
                        // Hanya tampilkan untuk baris berisi wanita usia 10-54 tahun
                        return true; // Column visibility hanya bisa berbeda per tabel, bukan per baris
                    }),

                Tables\Columns\IconColumn::make('has_jkn')
                    ->label('JKN')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_tuberculosis')
                    ->label('TBC')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_hypertension')
                    ->label('Hipertensi')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ]),

                Tables\Filters\SelectFilter::make('relationship')
                    ->label('Hubungan')
                    ->options([
                        'Kepala Keluarga' => 'Kepala Keluarga',
                        'Istri' => 'Istri',
                        'Suami' => 'Suami',
                        'Anak' => 'Anak',
                        'Menantu' => 'Menantu',
                        'Cucu' => 'Cucu',
                        'Orang Tua' => 'Orang Tua',
                        'Mertua' => 'Mertua',
                        'Pembantu' => 'Pembantu',
                        'Lainnya' => 'Lainnya',
                    ]),

                Tables\Filters\TernaryFilter::make('has_jkn')
                    ->label('Memiliki JKN'),

                Tables\Filters\TernaryFilter::make('has_tuberculosis')
                    ->label('Menderita TBC'),

                Tables\Filters\TernaryFilter::make('has_hypertension')
                    ->label('Menderita Hipertensi'),

                Tables\Filters\TernaryFilter::make('is_pregnant')
                    ->label('Sedang Hamil'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, Model $record): array {
                        // Simpan record saat ini untuk digunakan dalam menentukan visibilitas form
                        $this->currentRecord = $record;

                        // Tambahkan data umur yang dihitung
                        if (isset($data['birth_date'])) {
                            $data['age'] = Carbon::parse($data['birth_date'])->age;
                        }

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
