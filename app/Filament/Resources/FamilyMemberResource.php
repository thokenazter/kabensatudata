<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\FamilyMember;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use App\Observers\FamilyMemberObserver;
use App\Filament\Resources\FamilyMemberResource\Pages;

class FamilyMemberResource extends Resource
{
    protected static ?string $model = FamilyMember::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Anggota Keluarga';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Keluarga')
                    ->schema([
                        Forms\Components\Select::make('family_id')
                            ->label('Keluarga')
                            ->relationship('family', 'head_name', function ($query) {
                                return $query->with('building.village');
                            })
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->building->village->name} - No.{$record->building->building_number} - {$record->family_number} - {$record->head_name}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive(),
                    ]),

                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->debounce('20000ms') // Menunda pemrosesan hingga pengguna berhenti mengetik selama 500ms
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('slug', Str::slug($state));
                            }),

                        Forms\Components\Hidden::make('slug')
                            ->reactive(),

                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->maxLength(16)  // Menggunakan integer, bukan string
                            ->numeric()
                            ->helperText('Masukkan 16 digit NIK tanpa spasi'),

                        Forms\Components\TextInput::make('rm_number')
                            ->label('No. Rekam Medis')
                            ->disabled()
                            ->helperText('Nomor RM akan dibuat otomatis saat data disimpan')

                            ->enableVoiceInput(),

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
                            ->reactive()
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
                                    ->reactive()
                                    ->live(),
                            ]),

                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->required()
                            ->reactive()
                            ->live(),
                        Forms\Components\Select::make('marital_status')
                            ->label('Status Perkawinan')
                            ->options([
                                'Belum Kawin' => 'Belum Kawin',
                                'Kawin' => 'Kawin',
                                'Cerai Hidup' => 'Cerai Hidup',
                                'Cerai Mati' => 'Cerai Mati'
                            ])
                            ->visible(function (Get $get) {
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                $age = Carbon::parse($birthDate)->age;
                                // Tampilkan untuk orang yang cukup umur (misalnya > 15 tahun)
                                return $age > 15;
                            })
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

                        Forms\Components\Select::make('education')
                            ->label('Pendidikan Terakhir')
                            ->options([
                                'Tidak Pernah Sekolah' => 'Tidak Pernah Sekolah',
                                'Tidak Tamat SD/MI' => 'Tidak Tamat SD/MI',
                                'Tamat SD/MI' => 'Tamat SD/MI',
                                'Tamat SMP/MTs' => 'Tamat SMP/MTs',
                                'Tamat SMA/MA/SMK' => 'Tamat SMA/MA/SMK',
                                'Tamat D1/D2/D3' => 'Tamat D1/D2/D3',
                                'Tamat D4/S1' => 'Tamat D4/S1',
                                'Tamat S2/S3' => 'Tamat S2/S3'
                            ])
                            ->visible(function (Get $get) {
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                $age = Carbon::parse($birthDate)->age;
                                // Tampilkan untuk usia minimal 6 tahun
                                return $age >= 6;
                            }),
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
                            }),
                    ])
                    ->visible(fn(Get $get) => $get('gender') === 'Perempuan')
                    ->reactive(),

                Forms\Components\Section::make('Informasi Umum')
                    ->schema([
                        // Forms\Components\TextInput::make('occupation')
                        //     ->label('Pekerjaan')
                        //     ->maxLength(255)
                        //     ->visible(function (Get $get) {
                        //         $birthDate = $get('birth_date');
                        //         if (!$birthDate) {
                        //             return false;
                        //         }

                        //         $age = Carbon::parse($birthDate)->age;
                        //         return $age > 10;
                        //     }),
                        Forms\Components\Select::make('occupation')
                            ->label('Pekerjaan')
                            ->options([
                                'Tidak Kerja' => 'Tidak Kerja',
                                'Sekolah' => 'Sekolah',
                                'ASN' => 'ASN',
                                'TNI/Polri' => 'TNI/Polri',
                                'Honorer' => 'Honorer',
                                'Pegawai Swasta' => 'Pegawai Swasta',
                                'Nelayan' => 'Nelayan',
                                'Petani' => 'Petani',
                                'IRT' => 'IRT',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->visible(function (Get $get) {
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                $age = Carbon::parse($birthDate)->age;
                                return $age > 10;
                            }),
                    ]),

                Forms\Components\Section::make('Informasi Kesehatan')
                    ->schema([
                        Forms\Components\Toggle::make('has_jkn')
                            ->label('1. Memiliki kartu JKN?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark'),

                        Forms\Components\Toggle::make('is_smoker')
                            ->label('2. Apakah merokok?')
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
                            ->reactive(),

                        Forms\Components\Toggle::make('use_toilet')
                            ->label('3. Apakah buang air besar di jamban?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->reactive(),

                        Forms\Components\Toggle::make('use_water')
                            ->label('4. Menggunakan Air Bersih?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark'),


                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('has_tuberculosis')
                                    ->label('5. Pernah didiagnosis TBC Paru?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->reactive(),

                                Forms\Components\Toggle::make('takes_tb_medication_regularly')
                                    ->label('6. Minum obat TBC secara teratur?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->visible(fn(Get $get) => $get('has_tuberculosis') === true)
                                    ->reactive(),
                            ]),

                        Forms\Components\Toggle::make('has_chronic_cough')
                            ->label('7. Pernah menderita batuk berdahak lebih dari 2 minggu?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->reactive(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('has_hypertension')
                                    ->label('8. Didiagnosis tekanan darah tinggi?')
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
                                    ->reactive(),

                                Forms\Components\Toggle::make('takes_hypertension_medication_regularly')
                                    ->label('9. Minum obat darah tinggi secara teratur?')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark')
                                    ->visible(fn(Get $get) => $get('has_hypertension') === true)
                                    ->reactive(),
                            ]),
                    ]),

                Forms\Components\Section::make('Informasi Kesehatan Reproduksi & Anak')
                    ->schema([
                        Forms\Components\Toggle::make('uses_contraception')
                            ->label('11. Menggunakan alat kontrasepsi atau ikut program KB?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function (Get $get) {
                                // Ambil data yang diperlukan
                                $gender = $get('gender');
                                $birthDate = $get('birth_date');
                                $isPregnant = $get('is_pregnant');
                                $maritalStatus = $get('marital_status');

                                // Jika tanggal lahir tidak diisi, field tidak muncul
                                if (!$birthDate) {
                                    return false;
                                }

                                // Status perkawinan harus "Kawin" untuk menampilkan pertanyaan KB
                                if ($maritalStatus !== 'Kawin') {
                                    return false;
                                }

                                // Hitung umur
                                try {
                                    $age = Carbon::parse($birthDate)->age;

                                    // Untuk perempuan: usia 10-54 tahun, berstatus kawin, dan tidak hamil
                                    if ($gender === 'Perempuan') {
                                        return $age >= 10 && $age <= 54 && $isPregnant !== true;
                                    }

                                    // Untuk laki-laki: usia > 10 tahun dan berstatus kawin
                                    if ($gender === 'Laki-laki') {
                                        return $age > 10;
                                    }

                                    // Jika tidak memenuhi kondisi di atas
                                    return false;
                                } catch (\Exception $e) {
                                    // Jika ada error saat parsing tanggal, field tidak muncul
                                    return false;
                                }
                            })
                            ->live(),

                        // Perbaikan form fields dengan live() daripada reactive()

                        Forms\Components\Toggle::make('gave_birth_in_health_facility')
                            ->label('12. Melahirkan di fasilitas kesehatan?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function (Get $get) {
                                // Hanya muncul untuk perempuan
                                if ($get('gender') !== 'Perempuan') {
                                    return false;
                                }

                                // Hanya untuk Kepala Keluarga atau Istri
                                if (!in_array($get('relationship'), ['Kepala Keluarga', 'Istri'])) {
                                    return false;
                                }

                                // Idealnya kita mengecek apakah ada anak < 12 bulan
                                // Tapi kita bisa mengaktifkan untuk semua perempuan dewasa
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                $age = Carbon::parse($birthDate)->age;
                                return $age >= 18; // Tampilkan untuk wanita dewasa
                            })
                            ->live(),

                        Forms\Components\Toggle::make('exclusive_breastfeeding')
                            ->label('13. Bayi diberi ASI eksklusif 0-6 bulan?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function (Get $get) {
                                // Untuk anak berusia 7-23 bulan
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                // Harus berupa anak
                                if (!in_array($get('relationship'), ['Anak', 'Cucu'])) {
                                    return false;
                                }

                                try {
                                    $ageInMonths = Carbon::parse($birthDate)->diffInMonths(Carbon::now());
                                    return $ageInMonths >= 7 && $ageInMonths <= 23;
                                } catch (\Exception $e) {
                                    return false;
                                }
                            })
                            ->live(),

                        Forms\Components\Toggle::make('complete_immunization')
                            ->label('14. Imunisasi lengkap 0-11 bulan?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function (Get $get) {
                                // Untuk anak berusia 12-23 bulan
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                // Harus berupa anak
                                if (!in_array($get('relationship'), ['Anak', 'Cucu'])) {
                                    return false;
                                }

                                try {
                                    $ageInMonths = Carbon::parse($birthDate)->diffInMonths(Carbon::now());
                                    return $ageInMonths >= 12 && $ageInMonths <= 23;
                                } catch (\Exception $e) {
                                    return false;
                                }
                            })
                            ->live(),

                        Forms\Components\Toggle::make('growth_monitoring')
                            ->label('15. Pemantauan pertumbuhan balita dalam 1 bulan terakhir?')
                            ->inline(false)
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->visible(function (Get $get) {
                                // Untuk anak berusia 2-59 bulan
                                $birthDate = $get('birth_date');
                                if (!$birthDate) {
                                    return false;
                                }

                                // Harus berupa anak
                                if (!in_array($get('relationship'), ['Anak', 'Cucu'])) {
                                    return false;
                                }

                                try {
                                    $ageInMonths = Carbon::parse($birthDate)->diffInMonths(Carbon::now());
                                    return $ageInMonths >= 2 && $ageInMonths <= 59;
                                } catch (\Exception $e) {
                                    return false;
                                }
                            })
                            ->live(),
                    ])
                    ->visible(function (Get $get) {
                        $birthDate = $get('birth_date');
                        $gender = $get('gender');
                        $relationship = $get('relationship');

                        if (!$birthDate) {
                            return false;
                        }

                        try {
                            $age = Carbon::parse($birthDate)->age;

                            // Untuk perempuan 10-54 tahun yang berstatus istri/kepala keluarga
                            if ($gender === 'Perempuan') {
                                if ($age >= 10 && $age <= 54) {
                                    return in_array($relationship, ['Kepala Keluarga', 'Istri']);
                                }
                            }

                            // Untuk laki-laki 10+ tahun yang berstatus suami/kepala keluarga
                            if ($gender === 'Laki-laki') {
                                if ($age > 10) {
                                    return in_array($relationship, ['Kepala Keluarga', 'Suami']);
                                }
                            }

                            // Untuk anak di bawah 5 tahun (balita)
                            if (in_array($relationship, ['Anak', 'Cucu']) && $age < 5) {
                                return true;
                            }

                            return false;
                        } catch (\Exception $e) {
                            return false;
                        }
                    })
                    ->live(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('family.building.village.name')
                    ->label('Desa')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('family.building.building_number')
                    ->label('No Bangunan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('family.family_number')
                    ->label('No Keluarga')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('family.head_name')
                    ->label('Kepala Keluarga')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('relationship')
                    ->label('Hubungan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin'),

                Tables\Columns\TextColumn::make('marital_status')
                    ->label('Status Kawin')
                    ->searchable(),

                Tables\Columns\TextColumn::make('education')
                    ->label('Pendidikan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->date('d-m-Y'),

                Tables\Columns\TextColumn::make('age')
                    ->label('Umur')
                    ->state(function ($record) {
                        if ($record->birth_date) {
                            $birthDate = Carbon::parse($record->birth_date);
                            $now = Carbon::now();

                            // Jika usia kurang dari 1 tahun, tampilkan dalam bulan
                            if ($birthDate->diffInYears($now) < 1) {
                                // Pastikan nilai bulan berupa integer (bulat)
                                $ageInMonths = (int) $birthDate->diffInMonths($now);
                                return $ageInMonths . ' bulan';
                            } else {
                                // Pastikan nilai tahun berupa integer (bulat)
                                $ageInYears = (int) $birthDate->age;
                                return $ageInYears . ' tahun';
                            }
                        }
                        return '-';
                    }),

                Tables\Columns\IconColumn::make('is_pregnant')
                    ->label('Hamil')
                    ->boolean(),

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

                Tables\Filters\SelectFilter::make('family_id')
                    ->label('Keluarga')
                    ->relationship('family', 'head_name'),

                Tables\Filters\SelectFilter::make('village')
                    ->label('Desa')
                    ->relationship('family.building.village', 'name'),

                Tables\Filters\TernaryFilter::make('has_jkn')
                    ->label('Memiliki JKN'),

                Tables\Filters\TernaryFilter::make('has_tuberculosis')
                    ->label('Menderita TBC'),

                Tables\Filters\TernaryFilter::make('has_hypertension')
                    ->label('Menderita Hipertensi'),

                Tables\Filters\TernaryFilter::make('is_pregnant')
                    ->label('Sedang Hamil'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilyMembers::route('/'),
            'create' => Pages\CreateFamilyMember::route('/create'),
            'edit' => Pages\EditFamilyMember::route('/{record}/edit'),
        ];
    }
}
