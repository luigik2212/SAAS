<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\BillExportController;
use App\Http\Controllers\BillPrintController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::view('/login', 'auth.login')->name('login');
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('contas')->name('contas.')->group(function (): void {
        Route::get('/export/csv', [BillExportController::class, 'csv'])->name('export.csv');
        Route::get('/print', [BillPrintController::class, 'index'])->name('print');

        Route::get('/', [BillController::class, 'index'])->name('index');
        Route::post('/', [BillController::class, 'store'])->name('store');
        Route::put('/{bill}', [BillController::class, 'update'])->name('update');
        Route::delete('/{bill}', [BillController::class, 'destroy'])->name('destroy');
        Route::post('/{bill}/pay', [BillController::class, 'pay'])->name('pay');
        Route::post('/{bill}/reopen', [BillController::class, 'reopen'])->name('reopen');
    });
});
