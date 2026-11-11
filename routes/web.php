<?php

use App\Http\Controllers\StrukController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/transaksi/{transaksi}/struk', StrukController::class)
    ->middleware('auth')
    ->name('transaksi.struk');
