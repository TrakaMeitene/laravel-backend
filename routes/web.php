<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return '<a href="https://pierakstspie.lv">PierakstsPie.lv</a>';
});

Route::get('/success', action: [PaymentController::class, 'success'])->name('success-route');
Route::get('/fail', action: [PaymentController::class, 'fail'])->name('fail-route');


