<?php
// file: app/Filament/Resources/MedicalRecordResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalRecordResource\Pages;
use App\Models\MedicalRecord;
use App\Models\FamilyMember;
use App\Models\Medicine;
use App\Exports\MedicalRecordExport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Columns\TextColumn\TextColumnSize;

class MedicalRecordResource extends Resource
{
    protected static ?string $model = MedicalRecord::class;
    protected static ?string $navigationLabel = 'Rekam Medis';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    /** Helper: generate nomor antrian otomatis untuk tanggal tertentu */
    protected static function generateQueueNumberFor($date): string
    {
        return MedicalRecord::generateQueueNumberForDate($date);
    }

    /** Helper: parse "3dd1" → "3x1" (umum: {n}dd{m} → "{n}x{m}") */
    protected static function instructionToShortFrequency(?string $instr): ?string
    {
        if (blank($instr)) return null;
        if (preg_match('/^\s*(\d+)\s*dd\s*(\d+)\s*$/i', $instr, $m)) {
            return "{$m[1]}x{$m[2]}";
        }
        if (preg_match('/(\d+)\s*d+\s*(\d+)/i', $instr, $m)) {
            return "{$m[1]}x{$m[2]}";
        }
        return null;
    }

    /** Helper: satu-satunya sumber penyusun teks medication dari repeater (prioritas FREQUENCY). */
    protected static function composeMedicationText(?array $usages): string
    {
        if (empty($usages) || !is_array($usages)) return '';

        $ids = array_values(array_filter(array_column($usages, 'medicine_id')));
        $meds = collect();
        if ($ids) {
            $meds = Medicine::whereIn('id', $ids)->get()->keyBy('id');
        }

        $lines = [];
        foreach ($usages as $u) {
            $mid = $u['medicine_id'] ?? null;
            if (!$mid || !$meds->has($mid)) continue;

            $m = $meds[$mid];
            $line = $m->full_name;

            // Jumlah + unit (opsional)
            if (!empty($u['quantity_used'])) {
                $line .= ' - ' . $u['quantity_used'] . ' ' . $m->unit;
            }

            // Tampilkan preferensi: frequency (3x1) → fallback instruction_text (3dd1 → auto-parse → 3x1)
            $freq  = $u['frequency'] ?? null;
            $instr = $u['instruction_text'] ?? null;

            $display = null;
            if (!blank($freq)) {
                $display = $freq; // prioritas: frequency yang ditetapkan apoteker
            } elseif (!blank($instr)) {
                $display = self::instructionToShortFrequency($instr) ?? $instr;
            }

            if ($display) {
                $line .= " ({$display})";
            }

            // Catatan opsional
            if (!empty($u['notes'])) {
                $line .= ' - ' . $u['notes'];
            }

            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    /** Helper: get queue stats untuk dashboard */
    protected static function getQueueStats(): array
    {
        $today = now()->format('Y-m-d');

        return [
            'pending_registration' => MedicalRecord::whereDate('visit_date', $today)
                ->where('workflow_status', 'pending_registration')
                ->count(),
            'pending_nurse' => MedicalRecord::whereDate('visit_date', $today)
                ->where('workflow_status', 'pending_nurse')
                ->count(),
            'pending_doctor' => MedicalRecord::whereDate('visit_date', $today)
                ->where('workflow_status', 'pending_doctor')
                ->count(),
            'pending_pharmacy' => MedicalRecord::whereDate('visit_date', $today)
                ->where('workflow_status', 'pending_pharmacy')
                ->count(),
            'completed' => MedicalRecord::whereDate('visit_date', $today)
                ->where('workflow_status', 'completed')
                ->count(),
            'total_today' => MedicalRecord::whereDate('visit_date', $today)->count(),
        ];
    }

    /** Helper: get current serving queue number */
    protected static function getCurrentServingQueue(string $role): ?string
    {
        $statusMap = [
            'nurse' => 'pending_nurse',
            'doctor' => 'pending_doctor',
            'pharmacy' => 'pending_pharmacy',
        ];

        $status = $statusMap[$role] ?? null;
        if (!$status) return null;

        $record = MedicalRecord::where('workflow_status', $status)
            ->whereNotNull('current_role_handler')
            ->whereDate('visit_date', now())
            ->orderBy('queue_number')
            ->first();

        return $record?->queue_number;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // === Nomor Antrian Section (NEW)
                Forms\Components\Section::make('Informasi Antrian')
                    ->description('Nomor antrian otomatis untuk identifikasi pasien')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('queue_number')
                                ->label('Nomor Antrian')
                                ->default(fn(callable $get) => self::generateQueueNumberFor($get('visit_date') ?? now()))
                                ->disabled()
                                ->dehydrated()
                                ->prefix('🎯')
                                ->extraAttributes(['style' => 'font-weight: bold; font-size: 16px; color: #059669;']),

                            Forms\Components\TextInput::make('estimated_service_time')
                                ->label('Estimasi Waktu (menit)')
                                ->numeric()
                                ->default(15)
                                ->minValue(5)
                                ->maxValue(120)
                                ->suffix('menit'),

                            Forms\Components\Select::make('priority_level')
                                ->label('Prioritas')
                                ->options([
                                    'normal' => '🟢 Normal',
                                    'urgent' => '🟡 Mendesak',
                                    'emergency' => '🔴 Darurat'
                                ])
                                ->default('normal')
                                ->native(false),
                        ]),

                        // Queue Status Display (NEW)
                        Forms\Components\Placeholder::make('queue_status')
                            ->label('')
                            ->content(function (callable $get) {
                                $queueNumber = $get('queue_number');
                                if (!$queueNumber) return '';

                                $stats = self::getQueueStats();
                                $currentNurse = self::getCurrentServingQueue('nurse');
                                $currentDoctor = self::getCurrentServingQueue('doctor');
                                $currentPharmacy = self::getCurrentServingQueue('pharmacy');

                                return new \Illuminate\Support\HtmlString(
                                    '<div style="background: #f8fafc; padding: 12px; border-radius: 8px; font-family: monospace;">' .
                                        '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; text-align: center;">' .
                                        '<div><strong>👩‍⚕️ Perawat:</strong><br>' . ($currentNurse ?: 'Tidak ada') . '<br><small>Antri: ' . $stats['pending_nurse'] . '</small></div>' .
                                        '<div><strong>👨‍⚕️ Dokter:</strong><br>' . ($currentDoctor ?: 'Tidak ada') . '<br><small>Antri: ' . $stats['pending_doctor'] . '</small></div>' .
                                        '<div><strong>💊 Apoteker:</strong><br>' . ($currentPharmacy ?: 'Tidak ada') . '<br><small>Antri: ' . $stats['pending_pharmacy'] . '</small></div>' .
                                        '</div></div>'
                                );
                            })
                            ->visible(fn(callable $get) => filled($get('queue_number'))),
                    ])
                    ->collapsible(false)
                    ->compact(),

                // === Role Selection
                Forms\Components\Section::make('Pilih Role Kerja')
                    ->description('Pilih role untuk mengakses bagian form yang sesuai, atau aktifkan semua role untuk akses penuh')
                    ->schema([
                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\Checkbox::make('active_registration_role')
                                ->label('👩‍💼 Pendaftaran')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state && !$get('enable_all_roles')) {
                                        $set('workflow_status', 'pending_nurse');
                                        $set('registration_start_time', now());
                                    }
                                }),
                            Forms\Components\Checkbox::make('active_nurse_role')
                                ->label('👩‍⚕️ Perawat')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state && !$get('enable_all_roles')) {
                                        $set('workflow_status', 'pending_doctor');
                                        $set('current_role_handler', auth()->id());
                                        $set('nurse_start_time', now());
                                        if (!$get('registration_end_time')) {
                                            $set('registration_end_time', now());
                                        }
                                    }
                                }),
                            Forms\Components\Checkbox::make('active_doctor_role')
                                ->label('👨‍⚕️ Dokter')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state && !$get('enable_all_roles')) {
                                        $set('workflow_status', 'pending_pharmacy');
                                        $set('current_role_handler', auth()->id());
                                        $set('doctor_start_time', now());
                                        if (!$get('nurse_end_time')) {
                                            $set('nurse_end_time', now());
                                        }
                                    }
                                }),
                            Forms\Components\Checkbox::make('active_pharmacist_role')
                                ->label('💊 Apoteker')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state && !$get('enable_all_roles')) {
                                        $set('workflow_status', 'completed');
                                        $set('current_role_handler', auth()->id());
                                        $set('pharmacy_start_time', now());
                                        if (!$get('doctor_end_time')) {
                                            $set('doctor_end_time', now());
                                        }
                                    }
                                }),
                        ]),
                        Forms\Components\Checkbox::make('enable_all_roles')
                            ->label('☑️ Aktifkan Semua Role (Super User Mode)')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('active_registration_role', true);
                                    $set('active_nurse_role', true);
                                    $set('active_doctor_role', true);
                                    $set('active_pharmacist_role', true);
                                    $set('workflow_status', 'completed');
                                    $set('current_role_handler', auth()->id());
                                } else {
                                    $set('active_registration_role', false);
                                    $set('active_nurse_role', false);
                                    $set('active_doctor_role', false);
                                    $set('active_pharmacist_role', false);
                                    $set('workflow_status', 'pending_registration');
                                    $set('current_role_handler', null);
                                }
                            }),

                        // Hidden fields untuk tracking waktu dan petugas (NEW)
                        Forms\Components\Hidden::make('workflow_status')->default('pending_registration'),
                        Forms\Components\Hidden::make('current_role_handler'),
                        Forms\Components\Hidden::make('registration_start_time'),
                        Forms\Components\Hidden::make('registration_end_time'),
                        Forms\Components\Hidden::make('nurse_start_time'),
                        Forms\Components\Hidden::make('nurse_end_time'),
                        Forms\Components\Hidden::make('doctor_start_time'),
                        Forms\Components\Hidden::make('doctor_end_time'),
                        Forms\Components\Hidden::make('pharmacy_start_time'),
                        Forms\Components\Hidden::make('pharmacy_end_time'),
                    ])
                    ->collapsible(false)
                    ->compact(),

                // === Progress
                Forms\Components\Section::make('Progress Workflow')
                    ->schema([
                        Forms\Components\Placeholder::make('workflow_progress')
                            ->label('')
                            ->content(function (callable $get) {
                                $status = $get('workflow_status') ?? 'draft';
                                $handler = $get('current_role_handler');
                                $handlerName = $handler ? \App\Models\User::find($handler)?->name : null;

                                $labels = ['Pendaftaran', 'Perawat', 'Dokter', 'Apoteker', 'Selesai'];
                                $map = [
                                    'pending_registration' => 0,
                                    'pending_nurse' => 1,
                                    'pending_doctor' => 2,
                                    'pending_pharmacy' => 3,
                                    'completed' => 4,
                                ];
                                $cur = $map[$status] ?? 0;
                                $parts = [];
                                for ($i = 0; $i < 5; $i++) {
                                    $parts[] = $labels[$i] . ' ' . ($i <= $cur ? '●' : '○');
                                }

                                $progressBar = implode(' → ', $parts);
                                $handlerInfo = $handlerName && $status !== 'completed'
                                    ? "<br><small style='color: #059669;'>👤 Ditangani oleh: {$handlerName}</small>"
                                    : '';

                                return new \Illuminate\Support\HtmlString(
                                    '<div style="text-align:center;font-family:monospace;font-size:14px;color:#6B7280;">'
                                        . $progressBar . $handlerInfo . '</div>'
                                );
                            }),
                    ])
                    ->visible(fn(callable $get) => $get('workflow_status') !== 'draft')
                    ->collapsible(false)
                    ->compact(),

                // === Informasi Pasien
                Forms\Components\Section::make('Informasi Pasien')
                    ->description('Bagian ini dapat diakses oleh: Pendaftaran, atau Super User Mode')
                    ->visible(
                        fn(callable $get) =>
                        $get('enable_all_roles')
                            || $get('active_registration_role')
                            || $get('active_nurse_role')
                            || $get('active_doctor_role')
                            || $get('active_pharmacist_role')
                    )
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('family_member_id')
                                ->label('Anggota Keluarga')
                                ->relationship('familyMember', 'name')
                                ->searchable()->preload()->required()->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if (!$state) return;
                                    $fm = FamilyMember::with('family.building.village')->find($state);
                                    if (!$fm) return;
                                    $set('patient_name', $fm->name);
                                    $set('patient_gender', $fm->gender);
                                    $set('patient_nik', $fm->nik);
                                    $set('patient_rm_number', $fm->rm_number);
                                    $set('patient_birth_date', $fm->birth_date);
                                    $set('patient_age', $fm->age);
                                    if ($fm->family && $fm->family->building) {
                                        $b = $fm->family->building;
                                        $v = $b->village ?? null;
                                        $addr = $v
                                            ? "{$b->address}, {$v->name}, {$v->district}, {$v->regency}"
                                            : ($b->address ?? '');
                                        $set('patient_address', $addr);
                                    }
                                }),
                            Forms\Components\DatePicker::make('visit_date')
                                ->required()
                                ->default(now())
                                ->label('Tanggal Kunjungan')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('queue_number', self::generateQueueNumberFor($state ?: now()));
                                }),
                        ]),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('patient_name')->label('Nama Pasien')->disabled()->dehydrated(),
                            Forms\Components\TextInput::make('patient_gender')->label('Jenis Kelamin')->disabled()->dehydrated(),
                            Forms\Components\TextInput::make('patient_age')->label('Umur')->disabled()->dehydrated()->suffix('tahun'),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('patient_nik')->label('NIK')->disabled()->dehydrated(),
                            Forms\Components\TextInput::make('patient_rm_number')->label('No. RM')->disabled()->dehydrated(),
                        ]),
                        Forms\Components\Textarea::make('patient_address')->label('Alamat')->disabled()->dehydrated()->rows(2),
                    ])
                    ->collapsible(),

                // === Keluhan & Anamnesis
                Forms\Components\Section::make('Keluhan dan Anamnesis')
                    ->description('Bagian ini dapat diakses oleh: Perawat, Dokter, atau Super User Mode')
                    ->visible(
                        fn(callable $get) =>
                        $get('enable_all_roles') || $get('active_nurse_role') || $get('active_doctor_role')
                    )
                    ->schema([
                        Forms\Components\Textarea::make('chief_complaint')->label('Keluhan Utama')->rows(3),
                        Forms\Components\Textarea::make('anamnesis')->label('Anamnesis')->rows(4),
                    ])
                    ->collapsible(),

                // === Tanda Vital
                Forms\Components\Section::make('Tanda Vital')
                    ->description('Bagian ini dapat diakses oleh: Perawat, Dokter, atau Super User Mode')
                    ->visible(
                        fn(callable $get) =>
                        $get('enable_all_roles') || $get('active_nurse_role') || $get('active_doctor_role')
                    )
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('systolic')->label('Tekanan Darah Sistolik')->numeric()->suffix('mmHg'),
                            Forms\Components\TextInput::make('diastolic')->label('Tekanan Darah Diastolik')->numeric()->suffix('mmHg'),
                            Forms\Components\TextInput::make('heart_rate')->label('Detak Jantung')->numeric()->suffix('bpm'),
                        ]),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('body_temperature')->label('Suhu Tubuh')->numeric()->step(0.1)->suffix('°C'),
                            Forms\Components\TextInput::make('respiratory_rate')->label('Laju Pernapasan')->numeric()->suffix('/menit'),
                            Forms\Components\TextInput::make('weight')->label('Berat Badan')->numeric()->step(0.1)->suffix('kg'),
                        ]),
                        Forms\Components\TextInput::make('height')->label('Tinggi Badan')->numeric()->step(0.1)->suffix('cm'),
                    ])
                    ->collapsible(),

                // === Diagnosis & Tindakan (tanpa field medication di sini!)
                Forms\Components\Section::make('Diagnosis dan Tindakan')
                    ->description('Bagian ini dapat diakses oleh: Dokter atau Super User Mode')
                    ->visible(fn(callable $get) => $get('enable_all_roles') || $get('active_doctor_role'))
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('diagnosis_code')->label('Kode Diagnosis (ICD)')->placeholder('Contoh: A09'),
                            Forms\Components\TextInput::make('diagnosis_name')->label('Nama Diagnosis')->placeholder('Contoh: Diare'),
                        ]),
                        Forms\Components\Textarea::make('therapy')->label('Terapi')->rows(3),
                        Forms\Components\Textarea::make('procedure')->label('Tindakan')->rows(3),
                    ])
                    ->collapsible(),

                // === Resep Obat (repeater + medication text di sini)
                Forms\Components\Section::make('Resep Obat')
                    ->description('Dokter mengisi instruksi (mis. 3dd1). Apoteker mengonversi ke frequency singkat (mis. 3x1).')
                    ->visible(
                        fn(callable $get) =>
                        $get('enable_all_roles') || $get('active_doctor_role') || $get('active_pharmacist_role')
                    )
                    ->schema([
                        Forms\Components\Repeater::make('medicineUsages')
                            ->label('Daftar Obat')
                            ->relationship()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('medication', \App\Filament\Resources\MedicalRecordResource::composeMedicationText($state));
                            })
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\Select::make('medicine_id')
                                        ->label('Pilih Obat')
                                        ->options(function () {
                                            return Medicine::available()
                                                ->get()
                                                ->mapWithKeys(function ($medicine) {
                                                    $info = " (Stok: {$medicine->stock_quantity} {$medicine->unit})";
                                                    if (method_exists($medicine, 'isLowStock') && $medicine->isLowStock()) {
                                                        $info .= ' ⚠️';
                                                    }
                                                    return [$medicine->id => $medicine->full_name . $info];
                                                });
                                        })
                                        ->searchable()
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if (!$state) return;
                                            $m = Medicine::find($state);
                                            if (!$m) return;

                                            // Auto-saran instruksi & frequency
                                            $common = [
                                                'paracetamol' => '3dd1',
                                                'amoxicillin' => '3dd1',
                                                'ibuprofen'   => '3dd1',
                                                'antasida'    => '3dd1',
                                            ];
                                            $name = strtolower($m->name);
                                            $instr = null;
                                            foreach ($common as $key => $val) {
                                                if (str_contains($name, $key)) {
                                                    $instr = $val;
                                                    break;
                                                }
                                            }
                                            if ($instr) {
                                                $set('instruction_text', $instr);
                                                $short = \App\Filament\Resources\MedicalRecordResource::instructionToShortFrequency($instr);
                                                if ($short) $set('frequency', $short); // contoh: 3x1
                                            }

                                            // Default dosis opsional
                                            // $set('dosage', '1 ' . $m->unit);
                                        }),
                                    Forms\Components\TextInput::make('quantity_used')->label('Jumlah')->numeric()->required()->minValue(1),

                                    // Dokter isi instruksi; apoteker lihat saja (read-only)
                                    Forms\Components\TextInput::make('instruction_text')
                                        ->label('Instruksi (kode dokter, mis. 3dd1)')
                                        ->placeholder('3dd1, 2dd1, dll')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->disabled(fn(callable $get) => !$get('enable_all_roles') && $get('active_pharmacist_role'))
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $short = \App\Filament\Resources\MedicalRecordResource::instructionToShortFrequency($state);
                                            if ($short) $set('frequency', $short);
                                        }),
                                ]),
                                Forms\Components\Grid::make(2)->schema([
                                    // Apoteker menetapkan bentuk singkat yang dipakai ke DB
                                    Forms\Components\TextInput::make('frequency')
                                        ->label('Frekuensi (untuk DB), mis. 3x1')
                                        ->placeholder('3x1, 2x2, ...')
                                        ->live(onBlur: true),

                                    Forms\Components\TextInput::make('dosage')
                                        ->label('Dosis (opsional)')
                                        ->placeholder('1 tablet, 2 kapsul'),
                                ]),
                                Forms\Components\Textarea::make('notes')->label('Catatan')->placeholder('Diminum setelah makan, dll')->rows(2),
                            ])
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                if (!empty($state['medicine_id'])) {
                                    $m = Medicine::find($state['medicine_id']);
                                    if ($m) {
                                        $q = $state['quantity_used'] ?? '';
                                        $display = $state['frequency']
                                            ?? (MedicalRecordResource::instructionToShortFrequency($state['instruction_text'] ?? '') ?? ($state['instruction_text'] ?? ''));
                                        return "{$m->full_name} - {$q} {$m->unit}" . ($display ? " ({$display})" : '');
                                    }
                                }
                                return 'Obat Baru';
                            })
                            ->addActionLabel('Tambah Obat')
                            ->reorderableWithButtons()
                            ->cloneable(),

                        // === Medication text di sini (selalu dehydrated) ===
                        Forms\Components\Textarea::make('medication')
                            ->label('Resep Obat (Teks)')
                            ->rows(3)
                            ->helperText('Disusun otomatis: prioritas pakai FREKUENSI (3x1). Jika kosong, pakai instruksi (3dd1).')
                            ->dehydrated(true)
                            ->disabled(fn(callable $get) => !$get('enable_all_roles') && $get('active_pharmacist_role'))
                            ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                if (blank($state) && filled($get('medicineUsages'))) {
                                    $set('medication', \App\Filament\Resources\MedicalRecordResource::composeMedicationText($get('medicineUsages')));
                                }
                            }),
                    ])
                    ->collapsible(),

                // === Info Tambahan
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
                        // Nomor Antrian (NEW - paling prominent)
                        TextColumn::make('queue_number')
                            ->label('No. Antrian')
                            ->badge()
                            ->size(TextColumnSize::Large)
                            ->color(fn($record) => match ($record->priority_level ?? 'normal') {
                                'emergency' => 'danger',
                                'urgent' => 'warning',
                                default => 'success'
                            })
                            ->prefix('🎯 ')
                            ->sortable()
                            ->searchable(),

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
                            ->limit(50),
                        // Priority level (NEW)
                        TextColumn::make('priority_level')
                            ->label('Prioritas')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'emergency' => '🔴 Darurat',
                                'urgent' => '🟡 Mendesak',
                                default => '🟢 Normal'
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'emergency' => 'danger',
                                'urgent' => 'warning',
                                default => 'success'
                            }),
                    ]),

                    Stack::make([
                        TextColumn::make('diagnosis_name')
                            ->label('Diagnosis')
                            ->badge()
                            ->color('success'),
                        TextColumn::make('workflow_status')
                            ->label('Status Workflow')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'pending_registration' => 'Menunggu Pendaftaran',
                                'pending_nurse' => 'Menunggu Perawat',
                                'pending_doctor' => 'Menunggu Dokter',
                                'pending_pharmacy' => 'Menunggu Apoteker',
                                'completed' => 'Selesai',
                                default => 'Menunggu Pendaftaran'
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'pending_registration' => 'gray',
                                'pending_nurse' => 'info',
                                'pending_doctor' => 'warning',
                                'pending_pharmacy' => 'success',
                                'completed' => 'primary',
                                default => 'gray'
                            }),
                        // Current handler info (NEW)
                        TextColumn::make('currentHandler.name')
                            ->label('Ditangani Oleh')
                            ->color('gray')
                            ->prefix('👤 ')
                            ->placeholder('Belum ada'),
                        TextColumn::make('creator.name')
                            ->label('Dibuat Oleh')
                            ->color('gray'),
                    ]),
                ]),
            ])
            ->filters([
                // Filter berdasarkan nomor antrian (NEW)
                Filter::make('queue_number_search')
                    ->form([
                        Forms\Components\TextInput::make('queue_number')
                            ->label('Cari Nomor Antrian')
                            ->placeholder('2024-08-18-001')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['queue_number'],
                            fn(Builder $query, $queueNumber): Builder => $query->where('queue_number', 'like', "%{$queueNumber}%")
                        );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['queue_number'] ?? null) {
                            $indicators['queue_number'] = 'No. Antrian: ' . $data['queue_number'];
                        }
                        return $indicators;
                    }),

                // Filter priority level (NEW)
                SelectFilter::make('priority_level')
                    ->label('Prioritas')
                    ->options([
                        'normal' => '🟢 Normal',
                        'urgent' => '🟡 Mendesak',
                        'emergency' => '🔴 Darurat',
                    ]),

                Filter::make('visit_date_range')
                    ->form([
                        DatePicker::make('visit_from')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal mulai'),
                        DatePicker::make('visit_until')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['visit_from'] ?? null) {
                            $indicators['visit_from'] = 'Dari: ' . \Carbon\Carbon::parse($data['visit_from'])->format('d M Y');
                        }
                        if ($data['visit_until'] ?? null) {
                            $indicators['visit_until'] = 'Sampai: ' . \Carbon\Carbon::parse($data['visit_until'])->format('d M Y');
                        }
                        return $indicators;
                    }),

                SelectFilter::make('patient_gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ]),

                SelectFilter::make('diagnosis_name')
                    ->label('Diagnosis')
                    ->options(function () {
                        return MedicalRecord::whereNotNull('diagnosis_name')
                            ->distinct()
                            ->pluck('diagnosis_name', 'diagnosis_name')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('created_by')
                    ->label('Dibuat Oleh')
                    ->relationship('creator', 'name')
                    ->searchable(),

                SelectFilter::make('workflow_status')
                    ->label('Status Workflow')
                    ->options([
                        'pending_registration' => 'Menunggu Pendaftaran',
                        'pending_nurse' => 'Menunggu Perawat',
                        'pending_doctor' => 'Menunggu Dokter',
                        'pending_pharmacy' => 'Menunggu Apoteker',
                        'completed' => 'Selesai',
                    ]),

                SelectFilter::make('family_member_id')
                    ->label('Pasien')
                    ->relationship('familyMember', 'name')
                    ->searchable()
                    ->preload(),

                // Filter petugas yang menangani (NEW)
                SelectFilter::make('current_role_handler')
                    ->label('Petugas Aktif')
                    ->relationship('currentHandler', 'name')
                    ->searchable(),
            ])
            ->headerActions([
                // Quick Queue Search (NEW)
                Tables\Actions\Action::make('quick_queue_search')
                    ->label('Cari No. Antrian')
                    ->icon('heroicon-o-hashtag')
                    ->color('primary')
                    ->form([
                        Forms\Components\TextInput::make('queue_number')
                            ->label('Nomor Antrian')
                            ->placeholder('2024-08-18-001')
                            ->required()
                            ->autofocus(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $record = MedicalRecord::where('queue_number', $data['queue_number'])->first();
                        if ($record) {
                            return redirect()->route('filament.admin.resources.medical-records.edit', $record);
                        } else {
                            Notification::make()
                                ->title('Nomor Antrian Tidak Ditemukan')
                                ->body("Nomor antrian {$data['queue_number']} tidak ditemukan.")
                                ->warning()
                                ->send();
                        }
                    }),

                // Queue Dashboard (NEW)
                Tables\Actions\Action::make('queue_dashboard')
                    ->label('Dashboard Antrian')
                    ->icon('heroicon-o-tv')
                    ->color('info')
                    ->url(route('filament.admin.resources.medical-records.queue-dashboard'))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('patient_search')
                    ->label('Cari Riwayat Pasien')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('family_member_id')
                            ->label('Pilih Pasien')
                            ->relationship('familyMember', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Ketik nama pasien untuk mencari...'),
                    ])
                    ->action(function (array $data) {
                        $familyMember = \App\Models\FamilyMember::find($data['family_member_id']);
                        if ($familyMember && $familyMember->slug) {
                            return redirect()->route('medical-records.index', $familyMember->slug);
                        }
                        // Fallback jika slug tidak ada
                        return redirect()->route('medical-records.index', $data['family_member_id']);
                    }),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Forms\Components\Section::make('Filter Export')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        DatePicker::make('visit_from')
                                            ->label('Dari Tanggal')
                                            ->placeholder('Pilih tanggal mulai'),
                                        DatePicker::make('visit_until')
                                            ->label('Sampai Tanggal')
                                            ->placeholder('Pilih tanggal akhir'),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
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
                                    ]),
                                Forms\Components\DatePicker::make('medicine_report_month')
                                    ->label('Bulan Laporan Obat')
                                    ->displayFormat('F Y')
                                    ->default(now())
                                    ->native(false)
                                    ->helperText('Menentukan stok awal & pemakaian obat untuk bulan yang dipilih'),
                            ]),
                    ])
                    ->action(function (array $data) {
                        try {
                            $export = new MedicalRecordExport($data);
                            $timestamp = now()->format('Y-m-d-H-i-s');
                            $filename = "medical-records-{$timestamp}.xlsx";

                            return Excel::download($export, $filename);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Export Gagal')
                                ->body('Terjadi kesalahan saat export: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->form([
                        Forms\Components\Section::make('Filter Export')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        DatePicker::make('visit_from')
                                            ->label('Dari Tanggal')
                                            ->placeholder('Pilih tanggal mulai'),
                                        DatePicker::make('visit_until')
                                            ->label('Sampai Tanggal')
                                            ->placeholder('Pilih tanggal akhir'),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
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
                                    ]),
                                Forms\Components\DatePicker::make('medicine_report_month')
                                    ->label('Bulan Laporan Obat')
                                    ->displayFormat('F Y')
                                    ->default(now())
                                    ->native(false)
                                    ->helperText('Menentukan stok awal & pemakaian obat untuk bulan yang dipilih'),
                            ]),
                    ])
                    ->action(function (array $data) {
                        try {
                            $export = new MedicalRecordExport($data);
                            $timestamp = now()->format('Y-m-d-H-i-s');
                            $filename = "medical-records-{$timestamp}.csv";

                            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::CSV);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Export Gagal')
                                ->body('Terjadi kesalahan saat export: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document')
                    ->color('danger')
                    ->form([
                        Forms\Components\Section::make('Filter Export')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        DatePicker::make('visit_from')
                                            ->label('Dari Tanggal')
                                            ->placeholder('Pilih tanggal mulai'),
                                        DatePicker::make('visit_until')
                                            ->label('Sampai Tanggal')
                                            ->placeholder('Pilih tanggal akhir'),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
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
                                    ]),
                                Forms\Components\DatePicker::make('medicine_report_month')
                                    ->label('Bulan Laporan Obat')
                                    ->displayFormat('F Y')
                                    ->default(now())
                                    ->native(false)
                                    ->helperText('Menentukan stok awal & pemakaian obat untuk bulan yang dipilih'),
                            ]),
                    ])
                    ->action(function (array $data) {
                        try {
                            $export = new MedicalRecordExport($data);
                            $records = $export->collection();

                            $pdf = Pdf::loadView('exports.medical-records-pdf', [
                                'records' => $records,
                                'filters' => $data,
                                'timestamp' => now()->format('d F Y H:i:s')
                            ]);

                            $timestamp = now()->format('Y-m-d-H-i-s');
                            $filename = "medical-records-{$timestamp}.pdf";

                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->stream();
                            }, $filename);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Export Gagal')
                                ->body('Terjadi kesalahan saat export: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Quick Actions untuk Role (NEW)
                Tables\Actions\Action::make('serve_patient')
                    ->label('Layani Pasien')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(function (MedicalRecord $record) {
                        $userRoles = auth()->user()->roles->pluck('name');
                        return match ($record->workflow_status) {
                            'pending_nurse' => $userRoles->contains('nurse'),
                            'pending_doctor' => $userRoles->contains('doctor'),
                            'pending_pharmacy' => $userRoles->contains('pharmacist'),
                            default => false
                        };
                    })
                    ->action(function (MedicalRecord $record) {
                        $record->update([
                            'current_role_handler' => auth()->id(),
                            match ($record->workflow_status) {
                                'pending_nurse' => 'nurse_start_time',
                                'pending_doctor' => 'doctor_start_time',
                                'pending_pharmacy' => 'pharmacy_start_time',
                                default => null
                            } => now()
                        ]);

                        Notification::make()
                            ->title('Pasien Diambil')
                            ->body("Anda sekarang melayani pasien {$record->queue_number}")
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.medical-records.edit', $record);
                    }),

                Tables\Actions\Action::make('patient_history')
                    ->label('Riwayat Pasien')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->url(function (MedicalRecord $record): string {
                        $familyMember = $record->familyMember;
                        if ($familyMember && $familyMember->slug) {
                            return route('medical-records.index', $familyMember->slug);
                        }
                        // Fallback jika slug tidak ada
                        return route('medical-records.index', $record->family_member_id);
                    })
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('view_all_patient_records')
                    ->label('Semua Rekam Medis Pasien')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->modalHeading(fn(MedicalRecord $record) => 'Riwayat Rekam Medis - ' . $record->patient_name)
                    ->modalContent(fn(MedicalRecord $record) => view('medical-records.patient-history-modal', [
                        'records' => MedicalRecord::where('family_member_id', $record->family_member_id)
                            ->orderBy('visit_date', 'desc')
                            ->get(),
                        'patient' => $record
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('export_selected')
                        ->label('Export Terpilih')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('format')
                                ->label('Format Export')
                                ->options([
                                    'excel' => 'Excel (.xlsx)',
                                    'csv' => 'CSV (.csv)',
                                ])
                                ->default('excel')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            try {
                                $export = new MedicalRecordExport();
                                $timestamp = now()->format('Y-m-d-H-i-s');

                                if ($data['format'] === 'csv') {
                                    $filename = "selected-medical-records-{$timestamp}.csv";
                                    return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::CSV);
                                } else {
                                    $filename = "selected-medical-records-{$timestamp}.xlsx";
                                    return Excel::download($export, $filename);
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Export Gagal')
                                    ->body('Terjadi kesalahan saat export: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // Bulk update workflow status (NEW)
                    BulkAction::make('bulk_update_status')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('workflow_status')
                                ->label('Status Baru')
                                ->options([
                                    'pending_registration' => 'Menunggu Pendaftaran',
                                    'pending_nurse' => 'Menunggu Perawat',
                                    'pending_doctor' => 'Menunggu Dokter',
                                    'pending_pharmacy' => 'Menunggu Apoteker',
                                    'completed' => 'Selesai',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update([
                                'workflow_status' => $data['workflow_status'],
                                'current_role_handler' => $data['workflow_status'] !== 'completed' ? auth()->id() : null,
                            ]);

                            Notification::make()
                                ->title('Status Berhasil Diupdate')
                                ->body(count($records) . ' rekam medis berhasil diupdate.')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('queue_number', 'desc')
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

            // Queue pages
            'registration-queue' => Pages\RegistrationQueue::route('/registration-queue'),
            'nurse-queue' => Pages\NurseQueue::route('/nurse-queue'),
            'doctor-queue' => Pages\DoctorQueue::route('/doctor-queue'),
            'pharmacy-queue' => Pages\PharmacyQueue::route('/pharmacy-queue'),

            // NEW: Queue Dashboard & Display
            'queue-dashboard' => Pages\QueueDashboard::route('/queue-dashboard'),
            'queue-display' => Pages\QueueDisplay::route('/queue-display'),
            'current-serving' => Pages\CurrentServing::route('/current-serving/{role}'),
        ];
    }

    // NEW: Static methods untuk queue management
    public static function getNextQueueNumber(string $role): ?string
    {
        $statusMap = [
            'nurse' => 'pending_nurse',
            'doctor' => 'pending_doctor',
            'pharmacy' => 'pending_pharmacy',
        ];

        $status = $statusMap[$role] ?? null;
        if (!$status) return null;

        $record = MedicalRecord::where('workflow_status', $status)
            ->whereNull('current_role_handler')
            ->whereDate('visit_date', now())
            ->orderBy('queue_number')
            ->first();

        return $record?->queue_number;
    }

    public static function getTotalQueueLength(string $role): int
    {
        $statusMap = [
            'nurse' => 'pending_nurse',
            'doctor' => 'pending_doctor',
            'pharmacy' => 'pending_pharmacy',
        ];

        $status = $statusMap[$role] ?? null;
        if (!$status) return 0;

        return MedicalRecord::where('workflow_status', $status)
            ->whereDate('visit_date', now())
            ->count();
    }

    public static function getEstimatedWaitTime(string $role): int
    {
        $queueLength = self::getTotalQueueLength($role);
        $averageServiceTime = 15; // menit default

        // Hitung rata-rata waktu pelayanan berdasarkan data historis
        $avgTime = MedicalRecord::where('workflow_status', 'completed')
            ->whereDate('visit_date', '>=', now()->subDays(7))
            ->whereNotNull('estimated_service_time')
            ->avg('estimated_service_time');

        if ($avgTime) {
            $averageServiceTime = (int) $avgTime;
        }

        return $queueLength * $averageServiceTime;
    }
}
