<?php

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    return 'Optimize clear sudah Updtedte';
});

// Redirect root to dashboard
Route::redirect('/', '/dashboard');

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
Route::middleware(['auth'])->group(function () {
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
