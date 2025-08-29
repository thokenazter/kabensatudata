<?php

use App\Http\Controllers\Api\CrosstabController;
use App\Http\Controllers\ChatbotController;
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
