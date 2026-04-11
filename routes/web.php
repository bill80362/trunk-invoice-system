<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])
    ->name('invoices.print')
    ->middleware('auth');
