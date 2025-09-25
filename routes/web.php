<?php

use App\Http\Controllers\DatabaseBrowserController;
use App\Http\Controllers\DataPostgresController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');

    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    Route::get('/export/download', [ExportController::class, 'export'])->name('export.download');

    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    // Route::get('/users', [UserController::class, 'index'])->name('users.index');
    // Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/help', fn() => view('help'))->name('help');
    Route::get('/about', fn() => view('about'))->name('about');
    Route::get('/browser/schemas', [DatabaseBrowserController::class, 'listSchemas'])->name('browser.schemas');
    Route::get('/browser/{schema}/tables', [DatabaseBrowserController::class, 'listTables'])->name('browser.tables');
    Route::get('/browser/{schema}/{table}/preview', [DatabaseBrowserController::class, 'previewTable'])->name('browser.preview');

    Route::get('/postgres', [DataPostgresController::class, 'index'])->name('postgres.index');

    // Route untuk menampilkan PRATINJAU SATU tabel (Method: GET)
    Route::get('/postgres/{table}', [DataPostgresController::class, 'show'])->name('postgres.show');

    // Route untuk MEMPROSES EKSPOR tabel (Method: POST)
    Route::post('/postgres/export', [DataPostgresController::class, 'export'])->name('postgres.export');
});

require __DIR__ . '/auth.php';
