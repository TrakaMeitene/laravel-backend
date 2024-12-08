<?php

namespace App\Http\Controllers;

use App\Mail\CancelledBooking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\User;
use App\Models\Clients;
use Illuminate\Support\Facades\Mail;
use App\Mail\Booking as BookingMail;
use App\Mail\BookingSpecialist;
use App\Models\Invoice;

class BookingsController extends Controller
{
    public function Savebooking(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::parse($request->date);
        $specialist = User::find($request->specialist);
        if (Carbon::parse($date)->setTimezone('Europe/Riga')->format('H:i') == "00:00") {
            $date->addSecond();
        };
        
        switch ($user->scope) {
            case "all":
                $servicetime = $specialist->services->where('id', $request->service)->first();
                break;
            case "business":
                $servicetime = $user->services->where('id', $request->service)->first();
                break;
        }

        $time = $servicetime->time;
        $dateforend = clone $date->setTimezone('Europe/Riga');
        $end = $dateforend->addMinutes($time - 1);
        $allbookings = $user->bookings->where('statuss', 'active');

        //ja start nav tajā sarakstā, tad rodas problēma
        $include = $allbookings->whereBetween('end', [$date, $end])->whereBetween('date', [Carbon::parse($date)->startOfDay(), $end]); //ja viss periods iekļaujas, problēmu nav. ja sākums  ir ārpusē, tad ir problēma. 
        $includeend = $allbookings->whereBetween('date', [Carbon::parse($date)->startOfDay(), $end]); //ja viss periods iekļaujas, problēmu nav. ja sākums  ir ārpusē, tad ir problēma. 

        if ($include->isEmpty() && $includeend->isEmpty()) {
            $booking = Booking::create([
                'title' => $request->input('name'),
                'date' => $date->setTimezone('Europe/Riga'),
                'description' => $request->input('description'),
                'service' => $request->input('service'),
                'end' => $end,
                'user' => $specialist ? $specialist->id : $user->id,
                'made_by' => $user->id,
                'statuss' => 'active'
            ]);

            $clientexists = $specialist ? $specialist->clients->where('userid', $user->id) : $user->clients->where('userid', $user->id);
            if ($clientexists->isEmpty()) {
                Clients::Create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'specialist' => $specialist ? $specialist->id : $user->id,
                    'userid' => $user->id

                ]);
            }

            Mail::to($request->email, $specialist->email)->send(new BookingMail($booking, $specialist));
            Mail::to($specialist->email, $specialist->email)->send(new BookingSpecialist($booking, $user, $request, $servicetime));

        } else {
            $booking = 'Izvēlētajā laikā jau ir rezervācija. Lūdzu izvēlieties citu laiku.';
        }

        return $booking;

    }

    public function getbookings(Request $request)
    {
        $user = Auth::user();
        $bookings = $user->activeBookings;

        foreach ($bookings as $key => $value) {
            $value->time = Carbon::parse($value->date)->format('H:i');

            $value->date = Carbon::parse($value->date)->format('Y-m-d H:i:s');

        }

        return $bookings;
    }

    public function getbookingsUsermade(Request $request)
    {
        $user = Auth::user();
        $page = $request->current;
        $bookings = Booking::with(relations: ['specialist', 'service'])->where('made_by', $user->id)->latest()->paginate(7, ['*'], 'page', $page);

        foreach ($bookings as $key => $value) {

            $hoursBefore = Carbon::now()->diffInHours(Carbon::parse($value->date)->format('Y-m-d H:i:s'));
            $canCancel = $hoursBefore > 24 && $value->statuss === "active";
            $value['canCancel'] = $canCancel;
        }
        return $bookings;
    }

    public function cancelbooking(Request $request)
    {
        $booking = Booking::find($request->itemid);
        $client = User::find($booking->made_by);
        $specialist = User::find($booking->user);

        Booking::find($request->itemid)->update([
            'statuss' => "cancelled"
        ]);

        Invoice::where('booking', $request->itemid)->update([
            'status' => "cancelled"
        ]);
        //specialista atcelta vizite
        if ($request->cancelreason) {

            Mail::to($client->email)->send(new CancelledBooking($booking, $request, $client));

        } else {
            Mail::to($specialist->email)->send(new CancelledBooking($booking, $request, $client));

        }
    }
}
