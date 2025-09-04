<?php

use App\Http\Controllers\Api\CrosstabController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\MapBuildingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route untuk crosstab API
Route::middleware(['auth'])->group(function () {
    Route::get('/crosstab/variables', [CrosstabController::class, 'getVariables']);
    Route::post('/crosstab/data', [CrosstabController::class, 'getData']);
});

// Chatbot API routes
Route::post('/ask-chatbot', [ChatbotController::class, 'ask']);
Route::post('/crawl-website', [ChatbotController::class, 'crawlWebsite']);
Route::post('/upload-pdf', [ChatbotController::class, 'uploadPdf']);

// Map API routes - New BBOX and search endpoints
Route::prefix('map')->group(function () {
    // BBOX endpoint for efficient data loading
    Route::get('/buildings', [MapBuildingController::class, 'bbox'])
        ->name('api.map.buildings.bbox');
    
    // Search building by number
    Route::get('/buildings/find', [MapBuildingController::class, 'find'])
        ->name('api.map.buildings.find');
        
    // Stats endpoint (optional)
    Route::get('/stats', [MapBuildingController::class, 'stats'])
        ->name('api.map.stats');
});

// Aliases to fit frontend contracts
Route::get('/buildings', function (Request $request) {
    // Convert bounds={north,south,east,west} to bbox
    $bounds = $request->query('bounds');
    if ($bounds) {
        $decoded = is_string($bounds) ? json_decode($bounds, true) : $bounds;
        if (is_array($decoded) && isset($decoded['west'], $decoded['south'], $decoded['east'], $decoded['north'])) {
            $bbox = implode(',', [$decoded['west'], $decoded['south'], $decoded['east'], $decoded['north']]);
            $request->merge(['bbox' => $bbox]);
        }
    }
    $controller = app(MapBuildingController::class);
    return $controller->bbox($request);
});

Route::get('/buildings/{id}/families', function ($id) {
    // Reuse web endpoint logic for details
    return app(\App\Http\Controllers\MapController::class)->getBuildingDetails($id);
});

Route::get('/health-statistics/by-area', function (Request $request) {
    // Proxy to stats
    return app(MapBuildingController::class)->stats($request);
});

Route::post('/sync/changes', function (Request $request) {
    // Minimal ack endpoint for offline sync queue
    // TODO: Apply changes to DB if needed
    return response()->json(['ok' => true, 'received' => count($request->input('changes', []))]);
});
