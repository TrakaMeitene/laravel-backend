<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\User;
use App\Models\Clients;
use App\Mail\SampleMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\Booking as BookingMail;

class BookingsController extends Controller
{
    public function Savebooking(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::parse($request->date);
        $specialist = User::find($request->specialist);


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
        $allbookings = $user->bookings;
        $items = $allbookings->whereBetween('end', [$date, $end]);

        if ($items->isEmpty()) {
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
            $names = explode(' ', $request->title);

            $clientexists = $specialist ? $specialist->clients->where('userid', $user->id) : $user->clients->where('userid', $user->id);
            if($clientexists->isEmpty()){
            Clients::Create([
                'name' => $names[0],
                'surname' => $names[1],
                'phone' => $request->phone,
                'email' => $request->email,
                'specialist' => $specialist ? $specialist->id : $user->id,
                'userid' => $user->id

            ]);
            }

         Mail::to($request->email, $specialist->email)->send(new BookingMail($booking, $specialist));

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
            $value->time = Carbon::parse( $value->date)->format('H:i');

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
        Booking::find($request->itemid)->update([
            'statuss' => "cancelled"
        ]);


    }
}
