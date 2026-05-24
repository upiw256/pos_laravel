<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pos/receipt/{id}', [App\Http\Controllers\PosReceiptController::class, 'show'])->name('pos.receipt');
    Route::get('/pos/print-tags', [App\Http\Controllers\PriceTagController::class, 'print'])->name('pos.print-tags');
});
