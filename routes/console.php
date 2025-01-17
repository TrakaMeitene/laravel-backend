<?php

use App\Mail\Reminder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Service;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $bookings = Booking::with(relations: ['specialist', 'service'])->where('statuss', 'active')->whereBetween('date', [Carbon::now()->addDay()->startOfDay(), Carbon::now()->addDay()->endOfDay()])->get();
   info($bookings);
    foreach ($bookings as $booking) {
    $specialist = $booking->specialist;
    info($specialist->adress);
    $service = Service::where("id", $booking->service)->get();
        Mail::mailer('smtp')->to($booking->client)->send(new Reminder($booking, $service[0], $specialist));
    }

})->dailyAt('10:00');