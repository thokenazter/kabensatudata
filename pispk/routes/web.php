<?php

use App\Services\IksReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyMemberController;

// Route::get('/', function () {
//     return view('welcome');
// });

// Redirect root to dashboard
Route::redirect('/', '/dashboard');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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


// route untuk map
Route::get('/map', [MapController::class, 'index'])->name('map.index');
Route::get('/map/buildings', [MapController::class, 'getBuildingsData']);
Route::get('/map/buildings/{id}', [MapController::class, 'getBuildingDetails']);

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
