<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\User;
class BookingsController extends Controller
{
    public function Savebooking(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::parse($request->date);
        $specialist = User::find($request->specialist);


        switch($user->scope) {
            case "all":
                $servicetime = $specialist->services->where('id', $request->service)->first();
                break;
            case "business":
                $servicetime = $user->services->where('id', $request->service)->first();
                break;
        }

        $time = $servicetime->time;
        $dateforend = clone $date->setTimezone('Europe/Riga');
        $end = $dateforend->addMinutes($time);
        $allbookings = $user->bookings;
        $items = $allbookings->whereBetween('end', [$date, $end]);

        if ($items->isEmpty()) {
            $booking = Booking::create([
                'title' => $request->input('title'),
                'date' => $date->setTimezone('Europe/Riga'),
                'description' => $request->input('description'),
                'service' => $request->input('service'),
                'end' => $end,
                'user' => $specialist ? $specialist->id : $user->id,
                'made_by' => $user->id
            ]);
        } else {
            $booking = 'Izvēlētajā laikā jau ir rezervācija. Lūdzu izvēlieties citu laiku.';
        }
        return $booking;

    }

    public function getbookings(Request $request)
    {
        $user = Auth::user();
        $bookings = $user->bookings;

        foreach ($bookings as $key => $value) {
            $value->time = Carbon::parse(time: $value->date)->format('H:i');

            $value->date = Carbon::parse($value->date)->format('Y-m-d H:i:s');
        }

        return $bookings;
    }
}
