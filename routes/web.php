<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/terms', [LegalController::class, 'terms']);
Route::get('/privacy', [LegalController::class, 'privacy']);
Route::get('/about', [LegalController::class, 'about']);

Route::middleware('auth')->prefix('receipts')->name('receipts.')->group(function () {
    Route::get('/{booking}', [ReceiptController::class, 'view'])->name('view');
    Route::get('/{booking}/pdf', [ReceiptController::class, 'downloadPdf'])->name('pdf');
    Route::post('/{booking}/email', [ReceiptController::class, 'emailReceipt'])->name('email');
});
