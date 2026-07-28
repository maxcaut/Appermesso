<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/privacy/consenso-rifiutato', 'privacy-refused')->name('privacy.refused');

Route::post('/genera-pdf', [PdfController::class, '__invoke'])->name('pdf.generate');
