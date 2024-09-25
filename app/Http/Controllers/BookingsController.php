<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;

class BookingsController extends Controller
{
    public function Savebooking(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::parse($request->date);

        $servicetime = $user->services->where('id', $request->service);
        $time = $servicetime[0]->time;
        $dateforend = clone $date->setTimezone('Europe/Riga');
        $end = $dateforend->addMinutes($time);

        $booking = Booking::updateOrCreate([
            'title' => $request->input('title'),
            'date' => $date->setTimezone('Europe/Riga'),
            'description' => $request->input('description'),
            'service' => $request->input('service'),
            'end' => $end,
            'user' => $user->id,
        ]);

        return $booking;
    }

    public function getbookings(Request $request)
    {
        $user = Auth::user();
$data = $user->booking;
$bookings = $user->bookings;

foreach ($bookings as $key => $value) {
    $value->time = Carbon::parse(time: $value->date)->format('H:i');

    $value->date = Carbon::parse($value->date)->format('Y-m-d H:i:s');
}
info($bookings);

        return $bookings;
    }
}
