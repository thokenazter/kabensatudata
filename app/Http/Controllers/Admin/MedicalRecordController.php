<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MedicalRecordExport;
use App\Models\FamilyMember;
use App\Models\MedicalRecord;
use App\Models\Village;
use App\Models\Medicine;
use App\Models\MedicineUsage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalRecord::query()->with(['currentHandler', 'creator']);

        // Free text search (queue_number, patient_name, rm_number)
        if ($q = $request->string('q')->toString()) {
            $query->where(function ($qq) use ($q) {
                $qq->where('queue_number', 'like', "%{$q}%")
                    ->orWhere('patient_name', 'like', "%{$q}%")
                    ->orWhere('patient_rm_number', 'like', "%{$q}%");
            });
        }

        // Filters similar to Filament resource
        if ($status = $request->input('workflow_status')) {
            $query->where('workflow_status', $status);
        }
        if ($priority = $request->input('priority_level')) {
            $query->where('priority_level', $priority);
        }
        if ($gender = $request->input('patient_gender')) {
            $query->where('patient_gender', $gender);
        }
        if ($creator = $request->input('created_by')) {
            $query->where('created_by', $creator);
        }
        if ($familyId = $request->input('family_member_id')) {
            $query->where('family_member_id', $familyId);
        }
        if ($diag = $request->input('diagnosis_name')) {
            $query->where('diagnosis_name', $diag);
        }
        if ($from = $request->input('visit_from')) {
            $query->whereDate('visit_date', '>=', $from);
        }
        if ($until = $request->input('visit_until')) {
            $query->whereDate('visit_date', '<=', $until);
        }

        // Sorting
        $sort = $request->input('sort', 'queue_number');
        $dir = $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc';
        if (!in_array($sort, ['queue_number', 'visit_date', 'patient_name', 'created_at'])) {
            $sort = 'queue_number';
        }
        $records = $query->orderBy($sort, $dir)->paginate(15)->withQueryString();

        $diagnoses = MedicalRecord::whereNotNull('diagnosis_name')->distinct()->orderBy('diagnosis_name')->pluck('diagnosis_name', 'diagnosis_name');
        $users = User::orderBy('name')->pluck('name', 'id');
        $members = FamilyMember::orderBy('name')->limit(100)->pluck('name', 'id');

        return view('admin.medical-records.index', compact('records', 'sort', 'dir', 'diagnoses', 'users', 'members'));
    }

    public function create()
    {
        $medicines = Medicine::active()->orderBy('name')->get()->mapWithKeys(function ($m) {
            return [$m->id => $m->full_name . ' • ' . $m->unit];
        });
        $members = FamilyMember::orderBy('name')->limit(100)->pluck('name', 'id');
        $defaultDate = now()->toDateString();
        $record = new MedicalRecord([
            'visit_date' => $defaultDate,
            'queue_number' => MedicalRecord::generateQueueNumberForDate($defaultDate),
            'priority_level' => 'normal',
            'estimated_service_time' => 15,
        ]);
        $subIndicators = \App\Models\SpmSubIndicator::with('indicator')->orderBy('code')->get();
        return view('admin.medical-records.create', compact('record', 'medicines', 'members', 'subIndicators'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'family_member_id' => 'required|exists:family_members,id',
            'visit_date' => 'required|date',
            'chief_complaint' => 'nullable|string|max:255',
            'anamnesis' => 'nullable|string',
            'systolic' => 'nullable|integer|min:60|max:300',
            'diastolic' => 'nullable|integer|min:40|max:200',
            'weight' => 'nullable|numeric|min:0|max:500',
            'height' => 'nullable|numeric|min:0|max:300',
            'heart_rate' => 'nullable|integer|min:30|max:250',
            'body_temperature' => 'nullable|numeric|min:30|max:45',
            'respiratory_rate' => 'nullable|integer|min:5|max:60',
            'diagnosis_code' => 'nullable|string|max:255',
            'diagnosis_name' => 'nullable|string|max:255',
            'therapy' => 'nullable|string',
            'spm_sub_indicator_ids' => 'nullable|array',
            'spm_sub_indicator_ids.*' => 'integer|exists:spm_sub_indicators,id',
            'procedure' => 'nullable|string',
            'priority_level' => 'nullable|in:normal,urgent,emergency',
            'estimated_service_time' => 'nullable|integer|min:5|max:240',
            'workflow_status' => 'nullable|string',
            // Repeater
            'usages' => 'array',
            'usages.*.medicine_id' => 'nullable|exists:medicines,id',
            'usages.*.quantity_used' => 'nullable|integer|min:0',
            'usages.*.instruction_text' => 'nullable|string|max:255',
            'usages.*.frequency' => 'nullable|string|max:50',
            'usages.*.notes' => 'nullable|string|max:500',
        ]);

        $data['created_by'] = auth()->id();
        $data['workflow_status'] = $data['workflow_status'] ?? 'pending_registration';

        // Robust create with retry on unique queue_number collision
        $maxAttempts = 5;
        $attempt = 0;
        while (true) {
            try {
                DB::beginTransaction();

                // Generate queue number based on selected visit_date
                $data['queue_number'] = MedicalRecord::generateQueueNumberForDate($data['visit_date']);

                $record = new MedicalRecord($data);
                $record->save();

                // Sync SPM sub-indicators (multi)
                if (!empty($data['spm_sub_indicator_ids'] ?? [])) {
                    $record->spmSubIndicators()->sync($data['spm_sub_indicator_ids']);
                }

                // Sync denormalized patient data
                $record->syncPatientData();
                $record->save();

                // Save medicine usages
                foreach ($request->input('usages', []) as $u) {
                    if (empty($u['medicine_id'])) continue;
                    $record->medicineUsages()->create([
                        'medicine_id' => $u['medicine_id'],
                        'quantity_used' => $u['quantity_used'] ?? 0,
                        'instruction_text' => $u['instruction_text'] ?? null,
                        'frequency' => $u['frequency'] ?? null,
                        'notes' => $u['notes'] ?? null,
                    ]);
                }

                // Update medication text using model helper
                $record->load('medicineUsages.medicine');
                $record->medication = $record->generateMedicationText();
                $record->save();

                DB::commit();

                // Dispatch event untuk update data SPM
                \App\Events\MedicalRecordCreated::dispatch($record);
                break; // success
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                DB::rollBack();
                $attempt++;
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }
                usleep(random_int(10000, 50000)); // brief backoff before retry
                continue;
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle MySQL duplicate entry (1062) as well
                DB::rollBack();
                $attempt++;
                $isDuplicate = ($e->errorInfo[1] ?? null) === 1062;
                if ($isDuplicate && $attempt < $maxAttempts) {
                    usleep(random_int(10000, 50000));
                    continue;
                }
                throw $e;
            }
        }

        return redirect()->route('panel.medical-records.index')->with('success', 'Rekam medis berhasil dibuat');
    }

    public function edit(MedicalRecord $medicalRecord)
    {
        $medicalRecord->load(['medicineUsages', 'familyMember']);
        $medicines = Medicine::active()->orderBy('name')->get()->mapWithKeys(function ($m) {
            return [$m->id => $m->full_name . ' • ' . $m->unit];
        });
        $members = FamilyMember::orderBy('name')->limit(100)->pluck('name', 'id');
        $subIndicators = \App\Models\SpmSubIndicator::with('indicator')->orderBy('code')->get();
        return view('admin.medical-records.edit', [
            'record' => $medicalRecord,
            'medicines' => $medicines,
            'members' => $members,
            'subIndicators' => $subIndicators,
        ]);
    }

    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        $data = $request->validate([
            'family_member_id' => 'required|exists:family_members,id',
            'visit_date' => 'required|date',
            'chief_complaint' => 'nullable|string|max:255',
            'anamnesis' => 'nullable|string',
            'systolic' => 'nullable|integer|min:60|max:300',
            'diastolic' => 'nullable|integer|min:40|max:200',
            'weight' => 'nullable|numeric|min:0|max:500',
            'height' => 'nullable|numeric|min:0|max:300',
            'heart_rate' => 'nullable|integer|min:30|max:250',
            'body_temperature' => 'nullable|numeric|min:30|max:45',
            'respiratory_rate' => 'nullable|integer|min:5|max:60',
            'diagnosis_code' => 'nullable|string|max:255',
            'diagnosis_name' => 'nullable|string|max:255',
            'therapy' => 'nullable|string',
            'procedure' => 'nullable|string',
            'priority_level' => 'nullable|in:normal,urgent,emergency',
            'estimated_service_time' => 'nullable|integer|min:5|max:240',
            'workflow_status' => 'nullable|string',
            'spm_sub_indicator_ids' => 'nullable|array',
            'spm_sub_indicator_ids.*' => 'integer|exists:spm_sub_indicators,id',
            // Repeater
            'usages' => 'array',
            'usages.*.id' => 'nullable|integer',
            'usages.*.medicine_id' => 'nullable|exists:medicines,id',
            'usages.*.quantity_used' => 'nullable|integer|min:0',
            'usages.*.instruction_text' => 'nullable|string|max:255',
            'usages.*.frequency' => 'nullable|string|max:50',
            'usages.*.notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($data, $request, $medicalRecord) {
            $medicalRecord->fill($data);
            $medicalRecord->save();

            // Sync denormalized patient data if family_member_id changed
            if ($medicalRecord->wasChanged('family_member_id')) {
                $medicalRecord->syncPatientData();
                $medicalRecord->save();
            }

            // Sync usages: simple replace strategy
            $medicalRecord->medicineUsages()->delete();
            foreach ($request->input('usages', []) as $u) {
                if (empty($u['medicine_id'])) continue;
                $medicalRecord->medicineUsages()->create([
                    'medicine_id' => $u['medicine_id'],
                    'quantity_used' => $u['quantity_used'] ?? 0,
                    'instruction_text' => $u['instruction_text'] ?? null,
                    'frequency' => $u['frequency'] ?? null,
                    'notes' => $u['notes'] ?? null,
                ]);
            }

            $medicalRecord->load('medicineUsages.medicine');
            $medicalRecord->medication = $medicalRecord->generateMedicationText();
            $medicalRecord->save();

            // Sync SPM sub-indicators (multi)
            if (isset($data['spm_sub_indicator_ids'])) {
                $medicalRecord->spmSubIndicators()->sync($data['spm_sub_indicator_ids']);
            }
        });

        return redirect()->route('panel.medical-records.index')->with('success', 'Rekam medis berhasil diperbarui');
    }

    public function destroy(MedicalRecord $medicalRecord)
    {
        $medicalRecord->delete();
        return back()->with('success', 'Rekam medis dihapus');
    }

    public function take(MedicalRecord $medicalRecord)
    {
        $map = [
            'pending_nurse' => 'nurse_start_time',
            'pending_doctor' => 'doctor_start_time',
            'pending_pharmacy' => 'pharmacy_start_time',
            'pending_registration' => 'registration_start_time',
        ];
        $field = $map[$medicalRecord->workflow_status] ?? null;
        $medicalRecord->current_role_handler = auth()->id();
        if ($field) {
            $medicalRecord->{$field} = now();
        }
        $medicalRecord->save();
        return back()->with('success', 'Pasien diambil.');
    }

    public function completeStage(MedicalRecord $medicalRecord)
    {
        $medicalRecord->completeCurrentStage();
        return back()->with('success', 'Tahap diselesaikan.');
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');
        $filters = $request->only(['visit_from', 'visit_until', 'patient_gender', 'diagnosis_name']);
        $timestamp = now()->format('Y-m-d-H-i-s');

        if ($format === 'csv') {
            return Excel::download(new MedicalRecordExport($filters), "medical-records-{$timestamp}.csv", \Maatwebsite\Excel\Excel::CSV);
        }
        if ($format === 'pdf') {
            $export = new MedicalRecordExport($filters);
            $records = $export->collection();
            $pdf = Pdf::loadView('exports.medical-records-pdf', [
                'records' => $records,
                'filters' => $filters,
                'timestamp' => now()->format('d F Y H:i:s')
            ]);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, "medical-records-{$timestamp}.pdf");
        }

        return Excel::download(new MedicalRecordExport($filters), "medical-records-{$timestamp}.xlsx");
    }

    public function analytics(Request $request)
    {
        // Dropdown options
        $diagnoses = MedicalRecord::whereNotNull('diagnosis_name')
            ->select('diagnosis_name')
            ->distinct()
            ->orderBy('diagnosis_name')
            ->pluck('diagnosis_name', 'diagnosis_name');

        $villages = Village::orderBy('name')->pluck('name', 'id');

        // Default filter (bulan berjalan)
        $defaultFrom = now()->startOfMonth()->toDateString();
        $defaultUntil = now()->toDateString();

        return view('admin.medical-records.analytics', [
            'diagnoses' => $diagnoses,
            'villages' => $villages,
            'defaultFrom' => $defaultFrom,
            'defaultUntil' => $defaultUntil,
        ]);
    }

    public function analyticsData(Request $request)
    {
        $from = $request->input('visit_from');
        $until = $request->input('visit_until');
        $gender = $request->input('patient_gender');
        $diagnosis = $request->input('diagnosis_name');
        $villageId = $request->input('village_id');

        // Kunjungan per hari
        $visits = DB::table('medical_records')
            ->when($from, fn($q) => $q->whereDate('visit_date', '>=', $from))
            ->when($until, fn($q) => $q->whereDate('visit_date', '<=', $until))
            ->when($gender, fn($q) => $q->where('patient_gender', $gender))
            ->when($diagnosis, fn($q) => $q->where('diagnosis_name', $diagnosis))
            ->selectRaw('DATE(visit_date) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $visitsPerDay = [
            'type' => 'line',
            'labels' => $visits->pluck('day')->toArray(),
            'datasets' => [
                [
                    'label' => 'Kunjungan',
                    'data' => $visits->pluck('total')->toArray(),
                ],
            ],
        ];

        // Berdasarkan diagnosa (Top 10)
        $diagRows = DB::table('medical_records')
            ->when($from, fn($q) => $q->whereDate('visit_date', '>=', $from))
            ->when($until, fn($q) => $q->whereDate('visit_date', '<=', $until))
            ->when($gender, fn($q) => $q->where('patient_gender', $gender))
            ->when($diagnosis, fn($q) => $q->where('diagnosis_name', $diagnosis))
            ->selectRaw("COALESCE(NULLIF(TRIM(diagnosis_name), ''), 'Tidak diisi') as diag, COUNT(*) as total")
            ->groupBy('diag')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $byDiagnosis = [
            'type' => 'bar',
            'labels' => $diagRows->pluck('diag')->toArray(),
            'datasets' => [
                [
                    'label' => 'Jumlah',
                    'data' => $diagRows->pluck('total')->toArray(),
                ],
            ],
        ];

        // Berdasarkan jenis kelamin
        $genderRows = DB::table('medical_records')
            ->when($from, fn($q) => $q->whereDate('visit_date', '>=', $from))
            ->when($until, fn($q) => $q->whereDate('visit_date', '<=', $until))
            ->when($diagnosis, fn($q) => $q->where('diagnosis_name', $diagnosis))
            ->selectRaw("COALESCE(NULLIF(TRIM(patient_gender), ''), 'Tidak diisi') as gender, COUNT(*) as total")
            ->groupBy('gender')
            ->orderByDesc('total')
            ->get();

        $byGender = [
            'type' => 'pie',
            'labels' => $genderRows->pluck('gender')->toArray(),
            'datasets' => [
                [
                    'label' => 'Jenis Kelamin',
                    'data' => $genderRows->pluck('total')->toArray(),
                ],
            ],
        ];

        // Berdasarkan desa
        $villageRows = DB::table('medical_records')
            ->join('family_members', 'medical_records.family_member_id', '=', 'family_members.id')
            ->join('families', 'family_members.family_id', '=', 'families.id')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->join('villages', 'buildings.village_id', '=', 'villages.id')
            ->when($from, fn($q) => $q->whereDate('medical_records.visit_date', '>=', $from))
            ->when($until, fn($q) => $q->whereDate('medical_records.visit_date', '<=', $until))
            ->when($gender, fn($q) => $q->where('medical_records.patient_gender', $gender))
            ->when($diagnosis, fn($q) => $q->where('medical_records.diagnosis_name', $diagnosis))
            ->when($villageId, fn($q) => $q->where('villages.id', $villageId))
            ->selectRaw('villages.name as village, COUNT(*) as total')
            ->groupBy('villages.id', 'villages.name')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        $byVillage = [
            'type' => 'bar',
            'labels' => $villageRows->pluck('village')->toArray(),
            'datasets' => [
                [
                    'label' => 'Jumlah',
                    'data' => $villageRows->pluck('total')->toArray(),
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'visitsPerDay' => $visitsPerDay,
                'byDiagnosis' => $byDiagnosis,
                'byGender' => $byGender,
                'byVillage' => $byVillage,
            ],
        ]);
    }
}
