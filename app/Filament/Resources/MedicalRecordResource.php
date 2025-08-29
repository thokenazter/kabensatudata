<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalRecordResource\Pages;
use App\Filament\Resources\MedicalRecordResource\RelationManagers;
use App\Models\MedicalRecord;
use App\Models\FamilyMember;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class MedicalRecordResource extends Resource
{
    protected static ?string $model = MedicalRecord::class;
    protected static ?string $navigationLabel = 'Rekam Medis';

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pasien')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('family_member_id')
                                    ->label('Anggota Keluarga')
                                    ->relationship('familyMember', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $familyMember = FamilyMember::with('family.building.village')->find($state);
                                            if ($familyMember) {
                                                $set('patient_name', $familyMember->name);
                                                $set('patient_gender', $familyMember->gender);
                                                $set('patient_nik', $familyMember->nik);
                                                $set('patient_rm_number', $familyMember->rm_number);
                                                $set('patient_birth_date', $familyMember->birth_date);
                                                $set('patient_age', $familyMember->age);

                                                // Construct address
                                                if ($familyMember->family && $familyMember->family->building) {
                                                    $building = $familyMember->family->building;
                                                    $village = $building->village ?? null;

                                                    if ($village) {
                                                        $address = "{$building->address}, {$village->name}, {$village->district}, {$village->regency}";
                                                    } else {
                                                        $address = $building->address ?? '';
                                                    }
                                                    $set('patient_address', $address);
                                                }
                                            }
                                        }
                                    }),
                                Forms\Components\DatePicker::make('visit_date')
                                    ->required()
                                    ->default(now())
                                    ->label('Tanggal Kunjungan'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('patient_name')
                                    ->label('Nama Pasien')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('patient_gender')
                                    ->label('Jenis Kelamin')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('patient_age')
                                    ->label('Umur')
                                    ->disabled()
                                    ->dehydrated()
                                    ->suffix('tahun'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('patient_nik')
                                    ->label('NIK')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('patient_rm_number')
                                    ->label('No. RM')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        Forms\Components\Textarea::make('patient_address')
                            ->label('Alamat')
                            ->disabled()
                            ->dehydrated()
                            ->rows(2),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Keluhan dan Anamnesis')
                    ->schema([
                        Forms\Components\Textarea::make('chief_complaint')
                            ->label('Keluhan Utama')
                            ->rows(3),
                        Forms\Components\Textarea::make('anamnesis')
                            ->label('Anamnesis')
                            ->rows(4),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Tanda Vital')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('systolic')
                                    ->label('Tekanan Darah Sistolik')
                                    ->numeric()
                                    ->suffix('mmHg'),
                                Forms\Components\TextInput::make('diastolic')
                                    ->label('Tekanan Darah Diastolik')
                                    ->numeric()
                                    ->suffix('mmHg'),
                                Forms\Components\TextInput::make('heart_rate')
                                    ->label('Detak Jantung')
                                    ->numeric()
                                    ->suffix('bpm'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('body_temperature')
                                    ->label('Suhu Tubuh')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('�C'),
                                Forms\Components\TextInput::make('respiratory_rate')
                                    ->label('Laju Pernapasan')
                                    ->numeric()
                                    ->suffix('/menit'),
                                Forms\Components\TextInput::make('weight')
                                    ->label('Berat Badan')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('kg'),
                            ]),

                        Forms\Components\TextInput::make('height')
                            ->label('Tinggi Badan')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('cm'),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Diagnosis dan Tindakan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('diagnosis_code')
                                    ->label('Kode Diagnosis (ICD)')
                                    ->placeholder('Contoh: A09'),
                                Forms\Components\TextInput::make('diagnosis_name')
                                    ->label('Nama Diagnosis')
                                    ->placeholder('Contoh: Diare'),
                            ]),

                        Forms\Components\Textarea::make('therapy')
                            ->label('Terapi')
                            ->rows(3),
                        Forms\Components\Textarea::make('medication')
                            ->label('Obat')
                            ->rows(3),
                        Forms\Components\Textarea::make('procedure')
                            ->label('Tindakan')
                            ->rows(3),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('creator', 'name')
                            ->default(auth()->id())
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('patient_name')
                            ->label('Nama Pasien')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::Bold)
                            ->color('primary'),
                        TextColumn::make('patient_rm_number')
                            ->label('No. RM')
                            ->badge()
                            ->color('gray'),
                    ]),

                    Stack::make([
                        TextColumn::make('visit_date')
                            ->label('Tanggal Kunjungan')
                            ->date('d M Y')
                            ->sortable(),
                        TextColumn::make('chief_complaint')
                            ->label('Keluhan Utama')
                            ->limit(50)
                            ->tooltip(function (TextColumn $column): ?string {
                                $state = $column->getState();
                                if (strlen($state) <= 50) {
                                    return null;
                                }
                                return $state;
                            }),
                    ]),

                    Stack::make([
                        TextColumn::make('diagnosis_name')
                            ->label('Diagnosis')
                            ->badge()
                            ->color('success')
                            ->default('Belum ada diagnosis'),
                        TextColumn::make('blood_pressure_display')
                            ->label('Tekanan Darah')
                            ->getStateUsing(function ($record) {
                                if ($record->systolic && $record->diastolic) {
                                    return $record->systolic . '/' . $record->diastolic . ' mmHg';
                                }
                                return '-';
                            })
                            ->badge()
                            ->color(function ($state) {
                                if ($state === '-') return 'gray';
                                $parts = explode('/', $state);
                                if (count($parts) >= 2) {
                                    $systolic = (int) $parts[0];
                                    if ($systolic >= 140) return 'danger';
                                    if ($systolic >= 130) return 'warning';
                                    return 'success';
                                }
                                return 'gray';
                            }),
                    ]),

                    Stack::make([
                        TextColumn::make('bmi_display')
                            ->label('BMI')
                            ->getStateUsing(function ($record) {
                                if ($record->weight && $record->height) {
                                    $heightInMeters = $record->height / 100;
                                    $bmi = round($record->weight / ($heightInMeters * $heightInMeters), 1);
                                    return $bmi;
                                }
                                return '-';
                            })
                            ->badge()
                            ->color(function ($state) {
                                if ($state === '-') return 'gray';
                                $bmi = (float) $state;
                                if ($bmi < 18.5) return 'warning';
                                if ($bmi < 25) return 'success';
                                if ($bmi < 30) return 'warning';
                                return 'danger';
                            }),
                        TextColumn::make('creator.name')
                            ->label('Dibuat Oleh')
                            ->default('Tidak diketahui'),
                    ]),
                ])->from('md'),

                // Mobile view
                Stack::make([
                    TextColumn::make('patient_name')
                        ->weight(FontWeight::Bold)
                        ->color('primary'),
                    TextColumn::make('visit_date')
                        ->date('d M Y')
                        ->color('gray'),
                    TextColumn::make('chief_complaint')
                        ->limit(30),
                ])->visibleFrom('md')->hidden(),
            ])
            ->filters([
                SelectFilter::make('patient_gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ]),

                Filter::make('visit_date')
                    ->form([
                        DatePicker::make('visit_from')
                            ->label('Tanggal Kunjungan Dari'),
                        DatePicker::make('visit_until')
                            ->label('Tanggal Kunjungan Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['visit_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('visit_date', '>=', $date),
                            )
                            ->when(
                                $data['visit_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('visit_date', '<=', $date),
                            );
                    }),

                SelectFilter::make('diagnosis_name')
                    ->label('Diagnosis')
                    ->options(function () {
                        return MedicalRecord::whereNotNull('diagnosis_name')
                            ->distinct()
                            ->pluck('diagnosis_name', 'diagnosis_name')
                            ->toArray();
                    })
                    ->searchable(),

                Filter::make('has_vital_signs')
                    ->label('Memiliki Tanda Vital')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('systolic')),

                Filter::make('has_diagnosis')
                    ->label('Memiliki Diagnosis')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('diagnosis_name')),
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
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('visit_from')
                                    ->label('Tanggal Kunjungan Dari'),
                                Forms\Components\DatePicker::make('visit_until')
                                    ->label('Tanggal Kunjungan Sampai'),
                            ]),
                        Forms\Components\Select::make('patient_gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->placeholder('Semua'),
                        Forms\Components\Select::make('diagnosis_name')
                            ->label('Diagnosis')
                            ->options(function () {
                                return MedicalRecord::whereNotNull('diagnosis_name')
                                    ->distinct()
                                    ->pluck('diagnosis_name', 'diagnosis_name')
                                    ->toArray();
                            })
                            ->searchable()
                            ->placeholder('Semua'),
                    ])
                    ->action(function (array $data) {
                        $export = new \App\Exports\MedicalRecordExport();
                        $filename = $export->export($data);

                        return response()->download($filename, 'medical-records-' . now()->format('Y-m-d') . '.xlsx');
                    }),
            ])
            ->defaultSort('visit_date', 'desc')
            ->searchable()
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
            'index' => Pages\ListMedicalRecords::route('/'),
            'create' => Pages\CreateMedicalRecord::route('/create'),
            'view' => Pages\ViewMedicalRecord::route('/{record}'),
            'edit' => Pages\EditMedicalRecord::route('/{record}/edit'),
        ];
    }
}
