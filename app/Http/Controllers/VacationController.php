<?php

namespace App\Http\Controllers;

use App\Models\Vacation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonInterval;

class VacationController extends Controller
{
    public function SaveVacation(Request $request)
    {
        $user = Auth::user();
        $period =  CarbonInterval::days(1)->toPeriod(Carbon::parse($request->start)->setTimezone('Europe/Riga'), Carbon::parse($request->end)->setTimezone('Europe/Riga'));
         foreach ($period as $key => $value) {
             $newformat = Carbon::parse($value)->setTimezone('Europe/Riga')->format('Y-m-d ');

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
        if ($request->start) {
            $start = Carbon::parse($request->start)->setTimezone('Europe/Riga')->format('Y-m-d');
            $end = Carbon::parse($request->end)->setTimezone('Europe/Riga')->format('Y-m-d');
        } else {
            $start = Carbon::parse($request[0])->setTimezone('Europe/Riga')->format('Y-m-d');
            $end = Carbon::parse($request[6])->setTimezone('Europe/Riga')->format('Y-m-d');
        }
        //to do: parskatīt vai te strādā
        $items = $user->vacation->whereBetween('date', [$start, $end])->get();

        return $items;
    }

    public function GetVacationsbySpecId(Request $request)
    {
        $user = Auth::user();
        $page = $request->current;

        $vacations = $user->vacation()->paginate(7, ['*'], 'page', $page);
        return $vacations;
    }

    public function deletevacation(Request $request)
    {
        $user = Auth::user();
        $id = $request->id;

        $vacation = Vacation::findOrFail($id);
        if($vacation){
           $vacation->delete(); 
           return response()->json([
            'status' => 1,
            'msg' => 'Dati dzēsti veiksmīgi'
           ]);
           }
        else{
            return response()->json([
                'status' => 1,
                'msg' => 'Kaut kas nogāja greizi! Mēģini vēlreiz!'
               ]);        } 

    }

}