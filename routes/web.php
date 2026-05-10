<?php

use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\Web\ChecklistWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ReportWebController;
use App\Http\Controllers\Web\TemplateWebController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest routes — redirect authenticated users to dashboard
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Dashboard — accessible to any authenticated user
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Redirect root to dashboard
    Route::redirect('/', '/dashboard');

    /*
    |----------------------------------------------------------------------
    | Admin-only routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        // Templates
        Route::get('/templates', [TemplateWebController::class, 'index'])->name('templates.index');
        Route::get('/templates/create', [TemplateWebController::class, 'create'])->name('templates.create');
        Route::get('/templates/{template}/edit', [TemplateWebController::class, 'edit'])->name('templates.edit');

        // Reports
        Route::get('/reports', [ReportWebController::class, 'index'])->name('reports.index');
    });

    /*
    |----------------------------------------------------------------------
    | Auditor-only routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:auditor')->group(function () {
        // Checklists
        Route::get('/checklists', [ChecklistWebController::class, 'index'])->name('checklists.index');
        Route::get('/checklists/start', [ChecklistWebController::class, 'startIndex'])->name('checklists.start-index');
        Route::post('/checklists/start', [ChecklistWebController::class, 'start'])->name('checklists.start');
        Route::get('/checklists/{checklist}', [ChecklistWebController::class, 'show'])->name('checklists.show');
        Route::post('/checklists/{checklist}/save-draft', [ChecklistWebController::class, 'saveDraft'])->name('checklists.save-draft');
        Route::post('/checklists/{checklist}/submit', [ChecklistWebController::class, 'submit'])->name('checklists.submit');
    });

    /*
    |----------------------------------------------------------------------
    | PDF export — available to both roles (policy enforces ownership)
    |----------------------------------------------------------------------
    */
    Route::post('/checklists/{checklist}/export-pdf', [PdfExportController::class, 'export'])->name('checklists.export-pdf');
    Route::get('/checklists/{checklist}/download-pdf', [PdfExportController::class, 'download'])->name('checklists.download-pdf');
});
