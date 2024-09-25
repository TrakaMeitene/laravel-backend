<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Servicecontroller;
use App\Http\Controllers\SettingsController;

use App\Http\Controllers\VacationController;
use App\Http\Controllers\BookingsController;
use App\Models\Booking;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/register', [AuthController::class,'register']);
Route::post('/logins', [AuthController::class,'logins']);
Route::post('/logout', [AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('/user', [AuthController::class,'user'])->middleware('auth:sanctum');
Route::post('/updateuser', [AuthController::class,'updateuser'])->middleware('auth:sanctum');

Route::post('/addservice', [Servicecontroller::class,'addservice'])->middleware('auth:sanctum');
Route::get('/getservices', [Servicecontroller::class,'getservices'])->middleware('auth:sanctum');
Route::post('getservicebyid', [Servicecontroller::class, 'getservicebyid'])->middleware('auth:sanctum');

Route::post('/addsettings', [SettingsController::class,'addsettings'])->middleware('auth:sanctum');
Route::get('/getsettings', [SettingsController::class,'getsettings'])->middleware('auth:sanctum');

Route::post('/savevacation', [VacationController::class,'SaveVacation'])->middleware('auth:sanctum');
Route::post('/getvacation', [VacationController::class,'GetVacation'])->middleware('auth:sanctum');

Route::post('/savebooking', [BookingsController::class,'Savebooking'])->middleware('auth:sanctum');
Route::post('/getbookings', [BookingsController::class,'getbookings'])->middleware('auth:sanctum');

