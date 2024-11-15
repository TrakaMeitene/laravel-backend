<?php

use App\Mail\Booking;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;

Route::get('/', function () {
    return view('welcome');
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

