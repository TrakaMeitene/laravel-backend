<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VacationController extends Controller
{
    public function SaveVacation(Request $request)
    {
        $user = Auth::user();
        foreach ($request->all() as $key => $value) {

            $newformat = Carbon::parse($value)->setTimezone('Europe/Riga')->format('Y-m-d');
            
            $user->vacation()->Create([
                'date' => $newformat,
                'user' => $user->id,
            ]);
        }

        return $user->vacation->toJson();
    }

    public function GetVacation(Request $request)
    {
        $user = Auth::user();
        $start = Carbon::parse($request->start)->setTimezone('Europe/Riga')->format('Y-m-d');
        $end = Carbon::parse($request->end)->setTimezone('Europe/Riga')->format('Y-m-d');


        $items = Vacation::whereBetween('created_at', [$start, $end])->get();
        return $items;
    }
}