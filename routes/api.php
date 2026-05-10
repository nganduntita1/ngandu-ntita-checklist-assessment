<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (no authentication required)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Authenticated routes (Sanctum bearer token required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Templates — read access for both roles; mutations restricted to admin
    Route::get('/templates', [TemplateController::class, 'index']);
    Route::get('/templates/{template}', [TemplateController::class, 'show']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/templates', [TemplateController::class, 'store']);
        Route::put('/templates/{template}', [TemplateController::class, 'update']);
        Route::delete('/templates/{template}', [TemplateController::class, 'destroy']);
    });

    // Checklists — auditor only
    Route::middleware('role:auditor')->group(function () {
        Route::get('/checklists', [ChecklistController::class, 'index']);
        Route::post('/checklists/start', [ChecklistController::class, 'start']);
        Route::post('/checklists/{checklist}/save-draft', [ChecklistController::class, 'saveDraft']);
        Route::post('/checklists/{checklist}/submit', [ChecklistController::class, 'submit']);
    });

    // PDF export — moved to web routes (uses session auth)

    // Reports — admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/reports', [ReportController::class, 'index']);
    });
});
