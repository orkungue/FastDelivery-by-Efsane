<?php

use App\Http\Controllers\DeliveryNotePrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/delivery-notes/{deliveryNote}/print', DeliveryNotePrintController::class)
    ->name('delivery-notes.print');