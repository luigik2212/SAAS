<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::view('/login', 'auth.login')->name('login');
});

Route::middleware(['auth'])->group(function (): void {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::prefix('contas')->name('contas.')->group(function (): void {
        Route::get('/', [\App\Http\Controllers\ContaController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\ContaController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\ContaController::class, 'store'])->name('store');
        Route::get('/{conta}/edit', [\App\Http\Controllers\ContaController::class, 'edit'])->name('edit');
        Route::put('/{conta}', [\App\Http\Controllers\ContaController::class, 'update'])->name('update');
        Route::delete('/{conta}', [\App\Http\Controllers\ContaController::class, 'destroy'])->name('destroy');

        Route::patch('/{conta}/pagar', [\App\Http\Controllers\ContaStatusController::class, 'pagar'])->name('pagar');
        Route::patch('/{conta}/reabrir', [\App\Http\Controllers\ContaStatusController::class, 'reabrir'])->name('reabrir');

        Route::get('/export/csv', [\App\Http\Controllers\ContaExportController::class, 'csv'])->name('export.csv');
        Route::get('/print', [\App\Http\Controllers\ContaPrintController::class, 'index'])->name('print');
    });
});
