<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Auth;
use Illuminate\Http\Request;
use App\Models\Clients;
class ClientController extends Controller
{
    public function saveclient(Request $request)
    {
        $user = Auth::user();

        Clients::Create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'specialist' => $user->id,
            'userid' => null

        ]);

    }

    public function getclients(Request $request)
    {

        $page = $request->current;

        $clients = Clients::with(relations: ['specialist'])->paginate(7, ['*'], 'page', $page);
        return $clients;
    }

    public function clientvisited(Request $request)
    {
        //booking jāatzīme'kā apmeklētu.
        $bookingId = $request->data['id'];
        $booking = Booking::where('id', $bookingId)->first();
        $booking->update([
            'visited' => 'yes',
        ]);

        //jāpieskaita attendance clientu table. japarbauda vai booking user un made by nav vienādi, ja nav, tad pēc made_by sameklēt client tabulā, citādi pēc title
        if ($request->data['user'] == $request->data['made_by']) {
            $client = Clients::where('name', $request->data['title'])->first();
            //booking meklē pēc title
            $allbookings = Booking::where('title', $request->data['title'])->get();
        } else {
            $client = Clients::where('userid', $request->data['made_by'])->first();
            //bookings meklē pēc userid
            $allbookings = Booking::where('made_by',  $request->data['made_by'])->get();
                    }
            $client->increment('attended', 1);
        //jāaprēķina reitings un tas jāsaglabā 
        $totalcount = collect($allbookings)->count();
         $visitedcount = $client->attended;
         $rating = ($visitedcount/$totalcount) *100;
         $ratingvalue = 0;
         if ($rating >= 90) {
            $ratingvalue= 5;
        } elseif ($rating >= 75) {
            $ratingvalue= 4;
        } elseif ($rating >= 50) {
            $ratingvalue= 3;
        } elseif ($rating >= 25) {
            $ratingvalue= 2;
        } else {
            $ratingvalue= 1;
        }

        $client->update([
            'rating' => $ratingvalue
        ]);
info($rating);


    }
}
