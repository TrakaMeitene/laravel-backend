<?php

use App\Mail\Booking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return '<a href="https://pierakstspie.lv">PierakstsPie.lv</a>';
});

Route::get('send-mail', function () {
    $details = [
        'title' => 'Success',
        'content' => 'This is an email testing using Laravel',
    ];
   //return view('emails.email', ['content' => '19.11.2024 plkst. 8:00, Sandra jurberga-Šaudine. O.kalapka iela, Liepāja ']);
    Mail::to('sandra.jurberga@gmail.com')->send(new Booking($details));


    return 'Email sent at ' . now();
});

Route::get('/success', action: [PaymentController::class, 'success'])->name('success-route');
Route::get('/fail', action: [PaymentController::class, 'fail'])->name('fail-route');


