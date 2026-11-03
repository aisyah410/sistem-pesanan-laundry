<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PesananLaundryController;

Route::get('/pesanan-laundry', [PesananLaundryController::class, 'index'])
    ->name('pesanan-laundry.index');

Route::post('/pesanan-laundry', [PesananLaundryController::class, 'store'])
    ->name('pesanan-laundry.store');

Route::get('/pesanan-laundry/{id}/edit', [PesananLaundryController::class, 'edit'])
    ->name('pesanan-laundry.edit');

Route::put('/pesanan-laundry/{id}', [PesananLaundryController::class, 'update'])
    ->name('pesanan-laundry.update');

Route::delete('/pesanan-laundry/{id}', [PesananLaundryController::class, 'destroy'])
    ->name('pesanan-laundry.destroy');