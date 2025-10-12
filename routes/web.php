<?php

use App\Http\Controllers\ChatbotController;
use App\Services\IksReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\FamilyController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\SpmTargetController;
use App\Http\Controllers\SpmController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    return 'Optimize clear sudah Updtedte';
});

// Redirect root to dashboard
Route::redirect('/', '/dashboard');

// Ensure the default Laravel auth middleware can redirect to a valid login route
// When unauthenticated, Laravel calls route('login'). We alias it to Filament's login page.
Route::get('/login', function () {
    return redirect()->to('/admin/login');
})->name('login');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/simple-dashboard', function () {
    $stats = [
        'families' => \App\Models\Family::count(),
        'members' => \App\Models\FamilyMember::count(),
    ];
    $totalBuildings = \App\Models\Building::count();
    return view('dashboard.simple', compact('stats', 'totalBuildings'));
});

// Custom logout redirect
Route::get('/admin/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/dashboard');
})->name('custom-logout');

// Untuk menangani form POST logout dari Filament
Route::post('/admin/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/dashboard');
});

// Route dummy untuk mencegah error "Route [filament.admin.auth.logout] not defined"
Route::post('/filament/admin/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/dashboard');
})->name('filament.admin.auth.logout');

// Detail Info Anggota Keluarga
Route::get('/family-members/{familyMember}', [App\Http\Controllers\FamilyMemberController::class, 'show'])
    ->name('family-members.show');

Route::get('/about', function () {
    return view('pages.about');
});


// yang baru untuk analisa data multi variable

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/analisa', function () {
        return redirect('/admin/analisis');
    })->name('analisa');
});


// routes/web.php
Route::get('/analysis', [App\Http\Controllers\AnalysisController::class, 'index'])->name('analysis.index');
Route::post('/analysis/analyze', [App\Http\Controllers\AnalysisController::class, 'analyze'])->name('analysis.analyze');
Route::post('/analysis/export', [App\Http\Controllers\AnalysisController::class, 'export'])
    ->name('analysis.export')
    ->middleware(['auth', 'web']);


// route untuk map
Route::get('/map', [MapController::class, 'index'])->name('map.index');
Route::get('/map/buildings', [MapController::class, 'getBuildingsData']);
Route::get('/map/buildings/{id}', [MapController::class, 'getBuildingDetails']);

// Experimental Vue-based map (new UI)
Route::get('/map-vue', function () {
    return view('map.vue');
})->name('map.vue');

// Rute debugging untuk memeriksa fungsi API
Route::get('/debug/buildings/{id}', function ($id) {
    $building = \App\Models\Building::with([
        'village',
        'families.members' => function ($query) {
            $query->select('id', 'family_id', 'name', 'relationship', 'gender', 'birth_date');
        }
    ])->find($id);

    if (!$building) {
        return response()->json(['error' => 'Bangunan tidak ditemukan'], 404);
    }

    return response()->json($building);
});

// Chatbot Admin
Route::middleware(['auth'])->group(function () {
    Route::get('/test-chatbot', function () {
        return view('test-chatbot');
    })->name('test.chatbot');

    Route::get('/admin/chatbot', function () {
        // Define empty variables that are expected by the layout
        $educationStats = [];
        $genderStats = ['male' => 0, 'female' => 0];
        $ageStats = ['0-5' => 0, '6-12' => 0, '13-17' => 0, '18-30' => 0, '31-50' => 0, '>50' => 0];
        $maritalStats = [];
        $sanitationStats = ['clean_water_count' => 0, 'protected_water_count' => 0, 'toilet_count' => 0, 'sanitary_toilet_count' => 0];
        $waterStats = [];
        $jknStats = ['jkn_count' => 0, 'members' => 0];
        $kiaStats = [];
        $stats = [
            'families' => 0,
            'tbc_count' => 0,
            'hypertension_count' => 0,
            'chronic_cough_count' => 0,
            'mental_illness_count' => 0,
            'restrained_count' => 0
        ];
        $maternalStats = [
            'kb_count' => 0,
            'no_kb_count' => 0,
            'pregnant_count' => 0,
            'health_facility_birth_count' => 0
        ];
        $childStats = [
            'exclusive_breastfeeding_count' => 0,
            'complete_immunization_count' => 0,
            'growth_monitoring_count' => 0
        ];
        $jknByVillage = collect([]);

        return view('admin.chatbot', compact(
            'educationStats',
            'genderStats',
            'ageStats',
            'maritalStats',
            'sanitationStats',
            'waterStats',
            'jknStats',
            'kiaStats',
            'stats',
            'maternalStats',
            'childStats',
            'jknByVillage'
        ));
    })->name('admin.chatbot');

    Route::post('/admin/chatbot/sync-app-knowledge', [ChatbotController::class, 'syncAppKnowledge'])
        ->name('admin.chatbot.sync-app-knowledge');
});

// SPM Kesehatan
Route::middleware(['auth'])->group(function () {
    Route::get('/spm/dashboard', [SpmController::class, 'dashboard'])->name('spm.dashboard');
    Route::get('/spm/sub-indicators/{sub}/detail', [SpmController::class, 'subDetail'])->name('spm.sub.detail');
    Route::post('/spm/achievements/override', [\App\Http\Controllers\SpmAchievementController::class, 'store'])->name('spm.achievements.override');
    Route::resource('/spm/targets', SpmTargetController::class)->names([
        'index' => 'targets.index',
        'create' => 'targets.create',
        'store' => 'targets.store',
        'edit' => 'targets.edit',
        'update' => 'targets.update',
        'destroy' => 'targets.destroy',
        'show' => 'targets.show',
    ])->except(['show']);

    // Bulk edit targets
    Route::get('/spm/targets/bulk', [SpmTargetController::class, 'bulkEdit'])->name('targets.bulk');
    Route::post('/spm/targets/bulk', [SpmTargetController::class, 'bulkUpdate'])->name('targets.bulk.update');

    // Bulk edit monthly targets
    Route::get('/spm/targets/monthly/bulk', [\App\Http\Controllers\SpmMonthlyTargetController::class, 'bulkEdit'])->name('targets.monthly.bulk');
    Route::post('/spm/targets/monthly/bulk', [\App\Http\Controllers\SpmMonthlyTargetController::class, 'bulkUpdate'])->name('targets.monthly.bulk.update');
    Route::post('/spm/targets/monthly/auto-distribute', [\App\Http\Controllers\SpmMonthlyTargetController::class, 'autoDistribute'])->name('targets.monthly.auto');
});

// Diagnostik API
Route::get('/system/diagnostics', function () {
    // Cek keberadaan dan jumlah data di tabel utama
    $stats = [
        'database' => [
            'connection' => true,
            'tables' => [
                'buildings' => [
                    'count' => \App\Models\Building::count(),
                    'first_id' => \App\Models\Building::first() ? \App\Models\Building::first()->id : null,
                    'last_id' => \App\Models\Building::latest('id')->first() ? \App\Models\Building::latest('id')->first()->id : null,
                ],
                'villages' => [
                    'count' => \App\Models\Village::count(),
                ],
                'families' => [
                    'count' => \App\Models\Family::count(),
                ],
                'family_members' => [
                    'count' => DB::table('family_members')->count(),
                ],
            ],
        ],
        'endpoints' => [
            'buildings_list' => url('/map/buildings'),
            'building_detail_pattern' => url('/map/buildings/{id}'),
            'debug_building_pattern' => url('/debug/buildings/{id}'),
        ],
        'server' => [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ]
    ];

    return response()->json($stats);
});

Route::get('/api/check-auth', function () {
    return response()->json(['isLoggedIn' => Auth::check()]);
});

// API: Cari anggota keluarga (pasien) untuk typeahead pada panel Admin
Route::get('/api/family-members/search', function (\Illuminate\Http\Request $request) {
    abort_unless(Auth::check(), 403);
    $q = trim((string) $request->query('q', ''));
    if ($q === '') {
        return response()->json([]);
    }

    $members = \App\Models\FamilyMember::query()
        ->select(['id', 'name', 'nik', 'rm_number', 'gender', 'birth_date'])
        ->where(function ($w) use ($q) {
            $w->where('name', 'like', "%{$q}%")
              ->orWhere('nik', 'like', "%{$q}%")
              ->orWhere('rm_number', 'like', "%{$q}%");
        })
        ->orderBy('name')
        ->limit(15)
        ->get()
        ->map(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'rm_number' => $m->rm_number,
                'nik' => $m->nik,
                'gender' => $m->gender,
                'age' => method_exists($m, 'getAgeAttribute') ? $m->age : null,
            ];
        });

    return response()->json($members);
})->name('api.family-members.search');

// Route::get('/family-members/{id}/{slug?}', 'FamilyMemberController@show')
//     ->name('family-members.show');

// Route::get('/family-members/{family_member:slug}', [FamilyMemberController::class, 'show'])
//     ->name('family-members.show');


Route::get('/families/{family}/card', [App\Http\Controllers\FamilyController::class, 'showFamilyCard'])
    ->name('families.card');

// Route untuk melihat kartu keluarga dari anggota keluarga
Route::get('/family-members/{familyMember}/family-card', [App\Http\Controllers\FamilyController::class, 'showFamilyCardFromMember'])
    ->name('families.card.member');


Route::get('/crosstab', function () {
    return view('crosstab.index');
})->name('crosstab.index');

Route::get('/analysis/export-for-mymaps', [App\Http\Controllers\AnalysisController::class, 'exportForGoogleMyMaps'])
    ->name('analysis.export-for-mymaps')
    ->middleware('auth');


// Tambahkan routes berikut ke routes/web.php

// Routes untuk Family History
Route::middleware(['auth'])->group(function () {
    // Riwayat IKS
    Route::get('/families/{family}/history', [App\Http\Controllers\FamilyHistoryController::class, 'index'])
        ->name('families.history');
    Route::get('/families/{family}/history/{history}', [App\Http\Controllers\FamilyHistoryController::class, 'show'])
        ->name('families.history.show');

    // Prediksi IKS
    Route::post('/families/{family}/predict', [App\Http\Controllers\FamilyHistoryController::class, 'predictIks'])
        ->name('families.predict');

    // Generate rekomendasi
    Route::post('/families/{family}/recommendations/generate', [App\Http\Controllers\FamilyHistoryController::class, 'generateRecommendations'])
        ->name('families.recommendations.generate');

    // Print rekomendasi
    Route::get('/recommendations/{recommendation}/print', function () {
        return 'Fitur cetak belum diimplementasikan';
    })->name('recommendations.print');

    Route::post('/families/{family}/generate-recommendations', [App\Http\Controllers\FamilyHistoryController::class, 'generateRecommendations'])
        ->name('families.generate-recommendations');
});


// QR Code Routes
Route::get('/qrcode/family/{family}', [App\Http\Controllers\QRCodeController::class, 'familyCard'])
    ->name('qrcode.family');

Route::get('/qrcode/member/{familyMember}', [App\Http\Controllers\QRCodeController::class, 'familyMember'])
    ->name('qrcode.member');

Route::get('/family/{family}/qrcode', [App\Http\Controllers\QRCodeController::class, 'showFamilyQrPage'])
    ->name('family.qrcode');

Route::get('/qrcode/batch', [App\Http\Controllers\QRCodeController::class, 'batchPrintQrCodes'])
    ->name('qrcode.batch');



// Tambahkan ke routes/web.php atau routes/api.php
Route::get('/api/buildings', function (\Illuminate\Http\Request $request) {
    $villageId = $request->input('village_id');

    if (!$villageId) {
        return response()->json([]);
    }

    $buildings = App\Models\Building::where('village_id', $villageId)
        ->orderBy('building_number')
        ->get(['id', 'building_number']);

    return response()->json($buildings);
});

Route::get('/families/{family}/qrcode', [App\Http\Controllers\QRCodeController::class, 'singleFamilyQrCode'])
    ->name('families.qrcode');

// Tambahkan route ini di web.php
Route::get('/family-members/{familyMember}/qrcode', [App\Http\Controllers\QRCodeController::class, 'familyMemberQrCode'])
    ->name('families.qrcode.member');


// routes/web.php

// Medical Records Routes
Route::middleware(['auth', 'role:nakes|super_admin'])->group(function () {
    Route::get(
        '/family-members/{familyMember}/medical-records',
        [App\Http\Controllers\MedicalRecordController::class, 'index']
    )
        ->name('medical-records.index');

    Route::get(
        '/family-members/{familyMember}/medical-records/create',
        [App\Http\Controllers\MedicalRecordController::class, 'create']
    )
        ->name('medical-records.create');

    Route::post(
        '/family-members/{familyMember}/medical-records',
        [App\Http\Controllers\MedicalRecordController::class, 'store']
    )
        ->name('medical-records.store');

    Route::get(
        '/family-members/{familyMember}/medical-records/{medicalRecord}',
        [App\Http\Controllers\MedicalRecordController::class, 'show']
    )
        ->name('medical-records.show');

    Route::get(
        '/family-members/{familyMember}/medical-records/{medicalRecord}/edit',
        [App\Http\Controllers\MedicalRecordController::class, 'edit']
    )
        ->name('medical-records.edit');

    Route::put(
        '/family-members/{familyMember}/medical-records/{medicalRecord}',
        [App\Http\Controllers\MedicalRecordController::class, 'update']
    )
        ->name('medical-records.update');

    Route::get(
        '/family-members/{familyMember}/medical-records/{medicalRecord}/print-prescription',
        [App\Http\Controllers\MedicalRecordController::class, 'printPrescription']
    )
        ->name('medical-records.print-prescription');
});


// Tambahkan ke routes/web.php

// API Route untuk pencarian realtime
Route::get('/api/search', [App\Http\Controllers\SearchController::class, 'search'])
    ->name('api.search')
    ->middleware('auth'); // Pastikan user sudah login

// Route::middleware(['auth', 'can:view_any_medical_record'])->group(function () {
//     Route::get(
//         '/family-members/{familyMember}/medical-records',
//         [App\Http\Controllers\MedicalRecordController::class, 'index']
//     )
//         ->name('medical-records.index');

//     Route::get(
//         '/family-members/{familyMember}/medical-records/create',
//         [App\Http\Controllers\MedicalRecordController::class, 'create']
//     )
//         ->middleware('can:create_medical_record')
//         ->name('medical-records.create');
// });

// =============================
// Admin Panel (Custom) Routes
// =============================
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MedicineController as AdminMedicineController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VillageController as AdminVillageController;
use App\Http\Controllers\Admin\BuildingController as AdminBuildingController;
use App\Http\Controllers\Admin\FamilyController as AdminFamilyController;
use App\Http\Controllers\Admin\FamilyMemberController as AdminFamilyMemberController;
use App\Http\Controllers\Admin\MedicalRecordController as AdminMedicalRecordController;
use App\Http\Controllers\Pegawai\DashboardPegawaiController;
use App\Http\Controllers\Pegawai\PegawaiController as EmployeesController;
use App\Http\Controllers\Pegawai\PegawaiDokumenController as EmployeeDocumentsController;
use App\Http\Controllers\Pegawai\SuratTugasController as EmployeeSuratTugasController;
use App\Http\Controllers\Pegawai\SuratArchiveController as EmployeeSuratArchiveController;
use App\Http\Controllers\Pegawai\SuratController as EmployeeSuratController;

// NOTE: gunakan prefix 'panel' untuk menghindari tabrakan dengan Filament '/admin' saat transisi
Route::prefix('panel')
    ->name('panel.')
    ->middleware(['panel.auth'])
    ->group(function () {
        // Admin dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Medicines CRUD
        Route::get('/medicines', [AdminMedicineController::class, 'index'])->name('medicines.index');
        Route::get('/medicines/create', [AdminMedicineController::class, 'create'])->name('medicines.create');
        Route::post('/medicines', [AdminMedicineController::class, 'store'])->name('medicines.store');
        Route::get('/medicines/{medicine}/edit', [AdminMedicineController::class, 'edit'])->name('medicines.edit');
        Route::put('/medicines/{medicine}', [AdminMedicineController::class, 'update'])->name('medicines.update');
        Route::delete('/medicines/{medicine}', [AdminMedicineController::class, 'destroy'])->name('medicines.destroy');
        Route::post('/medicines/{medicine}/adjust', [AdminMedicineController::class, 'adjustStock'])->name('medicines.adjust');

        // Users CRUD
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Villages CRUD
        Route::get('/villages', [AdminVillageController::class, 'index'])->name('villages.index');
        Route::get('/villages/create', [AdminVillageController::class, 'create'])->name('villages.create');
        Route::post('/villages', [AdminVillageController::class, 'store'])->name('villages.store');
        Route::get('/villages/{village}/edit', [AdminVillageController::class, 'edit'])->name('villages.edit');
        Route::put('/villages/{village}', [AdminVillageController::class, 'update'])->name('villages.update');
        Route::delete('/villages/{village}', [AdminVillageController::class, 'destroy'])->name('villages.destroy');

        // Buildings CRUD
        Route::get('/buildings', [AdminBuildingController::class, 'index'])->name('buildings.index');
        Route::get('/buildings/create', [AdminBuildingController::class, 'create'])->name('buildings.create');
        Route::post('/buildings', [AdminBuildingController::class, 'store'])->name('buildings.store');
        Route::get('/buildings/{building}/edit', [AdminBuildingController::class, 'edit'])->name('buildings.edit');
        Route::put('/buildings/{building}', [AdminBuildingController::class, 'update'])->name('buildings.update');
        Route::delete('/buildings/{building}', [AdminBuildingController::class, 'destroy'])->name('buildings.destroy');

        // Families CRUD
        Route::get('/families', [AdminFamilyController::class, 'index'])->name('families.index');
        Route::get('/families/create', [AdminFamilyController::class, 'create'])->name('families.create');
        Route::post('/families', [AdminFamilyController::class, 'store'])->name('families.store');
        Route::get('/families/{family}/edit', [AdminFamilyController::class, 'edit'])->name('families.edit');
        Route::put('/families/{family}', [AdminFamilyController::class, 'update'])->name('families.update');
        Route::delete('/families/{family}', [AdminFamilyController::class, 'destroy'])->name('families.destroy');

        // Family Members CRUD
        Route::get('/family-members', [AdminFamilyMemberController::class, 'index'])->name('family-members.index');
        Route::get('/family-members/create', [AdminFamilyMemberController::class, 'create'])->name('family-members.create');
        Route::post('/family-members', [AdminFamilyMemberController::class, 'store'])->name('family-members.store');
        Route::get('/family-members/{familyMember}/edit', [AdminFamilyMemberController::class, 'edit'])->name('family-members.edit');
        Route::put('/family-members/{familyMember}', [AdminFamilyMemberController::class, 'update'])->name('family-members.update');
        Route::delete('/family-members/{familyMember}', [AdminFamilyMemberController::class, 'destroy'])->name('family-members.destroy');

        // Medical Records (Admin panel CRUD)
        Route::get('/medical-records', [AdminMedicalRecordController::class, 'index'])->name('medical-records.index');
        Route::get('/medical-records/create', [AdminMedicalRecordController::class, 'create'])->name('medical-records.create');
        Route::post('/medical-records', [AdminMedicalRecordController::class, 'store'])->name('medical-records.store');
        Route::get('/medical-records/{medicalRecord}/edit', [AdminMedicalRecordController::class, 'edit'])->name('medical-records.edit');
        Route::put('/medical-records/{medicalRecord}', [AdminMedicalRecordController::class, 'update'])->name('medical-records.update');
        Route::delete('/medical-records/{medicalRecord}', [AdminMedicalRecordController::class, 'destroy'])->name('medical-records.destroy');
        Route::post('/medical-records/{medicalRecord}/take', [AdminMedicalRecordController::class, 'take'])->name('medical-records.take');
        Route::post('/medical-records/{medicalRecord}/complete', [AdminMedicalRecordController::class, 'completeStage'])->name('medical-records.complete');
        Route::get('/medical-records/export', [AdminMedicalRecordController::class, 'export'])->name('medical-records.export');

        // Medical Records Analytics (Admin)
        Route::get('/medical-records/analytics', [AdminMedicalRecordController::class, 'analytics'])->name('medical-records.analytics');
        Route::get('/medical-records/analytics/data', [AdminMedicalRecordController::class, 'analyticsData'])->name('medical-records.analytics-data');
    });

// =============================
// Dashboard Pegawai (Custom)
// =============================
Route::prefix('pegawai')
    ->name('pegawai.')
    ->middleware(['auth', 'role:super_admin|pegawai'])
    ->group(function () {
        // Dashboard utama pegawai
        Route::get('/', [DashboardPegawaiController::class, 'index'])->name('dashboard');

        // CRUD Pegawai (Non-Filament)
        Route::get('/employees', [EmployeesController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [EmployeesController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeesController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}/edit', [EmployeesController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeesController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [EmployeesController::class, 'destroy'])->name('employees.destroy');

        // Dokumen Pegawai
        Route::get('/employees/{employee}/documents', [EmployeeDocumentsController::class, 'index'])->name('employees.documents.index');
        Route::get('/employees/{employee}/documents/create', [EmployeeDocumentsController::class, 'create'])->name('employees.documents.create');
        Route::post('/employees/{employee}/documents', [EmployeeDocumentsController::class, 'store'])->name('employees.documents.store');
        Route::get('/employees/{employee}/documents/{document}/edit', [EmployeeDocumentsController::class, 'edit'])->name('employees.documents.edit');
        Route::put('/employees/{employee}/documents/{document}', [EmployeeDocumentsController::class, 'update'])->name('employees.documents.update');
        Route::delete('/employees/{employee}/documents/{document}', [EmployeeDocumentsController::class, 'destroy'])->name('employees.documents.destroy');

        // Surat Tugas (generator)
        Route::get('/employees/{employee}/surat-tugas/create', [EmployeeSuratTugasController::class, 'create'])->name('employees.surat-tugas.create');
        Route::post('/employees/{employee}/surat-tugas', [EmployeeSuratTugasController::class, 'store'])->name('employees.surat-tugas.store');

        // E‑Arsip Surat
        Route::get('/surat-arsip', [EmployeeSuratArchiveController::class, 'index'])->name('surat-arsip.index');
        Route::get('/surat-arsip/upload', [EmployeeSuratArchiveController::class, 'create'])->name('surat-arsip.create');
        Route::post('/surat-arsip/upload', [EmployeeSuratArchiveController::class, 'store'])->name('surat-arsip.store');
        Route::get('/surat-arsip/{archive}/download', [EmployeeSuratArchiveController::class, 'download'])->name('surat-arsip.download');
        Route::get('/surat-arsip/{archive}/edit', [EmployeeSuratArchiveController::class, 'edit'])->name('surat-arsip.edit');
        Route::put('/surat-arsip/{archive}', [EmployeeSuratArchiveController::class, 'update'])->name('surat-arsip.update');
        Route::delete('/surat-arsip/{archive}', [EmployeeSuratArchiveController::class, 'destroy'])->name('surat-arsip.destroy');
        // PDF routes removed per request

        // Pembuatan Surat (umum)
        Route::get('/surat/tugas/create', [EmployeeSuratController::class, 'createTugas'])->name('surat.tugas.create');
        Route::post('/surat/tugas', [EmployeeSuratController::class, 'storeTugas'])->name('surat.tugas.store');
    });
