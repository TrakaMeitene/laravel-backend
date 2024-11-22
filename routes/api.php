<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Servicecontroller;
use App\Http\Controllers\SettingsController;

use App\Http\Controllers\VacationController;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\SpecialistsController;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class,'register']);
Route::post('/logins', [AuthController::class,'logins']);
Route::post('/logout', [AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('/user', [AuthController::class,'user'])->middleware('auth:sanctum');
Route::post('/updateuser', [AuthController::class,'updateuser'])->middleware('auth:sanctum');
// Route::get('login/facebook', [AuthController::class, 'redirectToFacebook']);
// Route::get('login/facebook/callback', [AuthController::class, 'handleFacebookCallback']);
// Route::get('login/google', [AuthController::class, 'redirectToGoogle']);
// Route::get('login/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/recoveremail', [AuthController::class,'recoveremail']);
Route::post('/passwordreset', [AuthController::class,'passwordreset'])->name('password.reset');


Route::post('/addservice', [Servicecontroller::class,'addservice'])->middleware('auth:sanctum');
Route::get('/getservices', [Servicecontroller::class,'getservices'])->middleware('auth:sanctum');
Route::post('getservicebyid', [Servicecontroller::class, 'getservicebyid'])->middleware('auth:sanctum');
Route::post( '/getservicesforspecialist', [Servicecontroller::class,'getservicesforspecialist']);
Route::delete('/deleteservice/{id}', [Servicecontroller::class, 'deleteservice']);

Route::post('/addsettings', [SettingsController::class,'addsettings'])->middleware('auth:sanctum');
Route::get('/getsettings', [SettingsController::class,'getsettings'])->middleware('auth:sanctum');

Route::post('/savevacation', [VacationController::class,'SaveVacation'])->middleware('auth:sanctum');
Route::post('/getvacation', [VacationController::class,'GetVacation'])->middleware('auth:sanctum');

Route::post('/savebooking', [BookingsController::class,'Savebooking'])->middleware('auth:sanctum');
Route::post('/getbookings', [BookingsController::class,'getbookings'])->middleware('auth:sanctum');
Route::post('/getbookingsUsermade', [BookingsController::class, 'getbookingsUsermade'])->middleware('auth:sanctum');
Route::post('/cancelbooking', [BookingsController::class, 'cancelbooking']);

Route::post('/getspecialists', [SpecialistsController::class, 'getspecialists']);
Route::post('/getspecialistbyname', [SpecialistsController::class,'getspecialistbyname']);
Route::post('/getspecialiststimes', [SpecialistsController::class,'getSpecialistsTimes']);
Route::post('/getspecialistbyid', [SpecialistsController::class,'getspecialistbyid']);

Route::post('/makeinvoice', action: [InvoiceController::class,'makeinvoice']);
Route::post('/getCustomerInvoices', action: [InvoiceController::class,'getCustomerInvoices'])->middleware('auth:sanctum');
Route::post('/getSpecialistInvoices', [InvoiceController::class,'getSpecialistInvoices'])->middleware('auth:sanctum');
Route::post('/updateInvoice', action: [InvoiceController::class,'updateInvoice'])->middleware('auth:sanctum');
Route::post('/getsumm', action: [InvoiceController::class,'getsumm'])->middleware('auth:sanctum');
Route::post('/saveexternalinvoice', action: [InvoiceController::class,'saveexternalinvoice'])->middleware('auth:sanctum');


Route::post('/saveclient', action: [ClientController::class,'saveclient'])->middleware('auth:sanctum');
Route::post('/getclients', action: [ClientController::class,'getclients'])->middleware('auth:sanctum');


