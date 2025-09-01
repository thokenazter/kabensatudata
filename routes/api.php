<?php

use App\Http\Controllers\Api\CrosstabController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\MapBuildingController;
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
