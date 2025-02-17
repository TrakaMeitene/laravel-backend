<?php

namespace App\Http\Controllers;

use App\Models\Special_availabilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SettingsController extends Controller
{

    public function addsettings(Request $request)
    {

        $user = Auth::user();

        foreach ($request->all() as $key => $value) {
            $user->settings()->updateOrCreate(['day' => $value['day']], $value);
        }

        return $user->settings->toJson();
    }

    public function getsettings(Request $request)
    {
        $user = Auth::user();
        $settings = $user->settings;

        return $settings;
    }

    public function saveSpecialtimes(Request $request)
    {
        $user = Auth::user();

        $specialtimes = Special_availabilities::Create([
            'service' => $request->data['service'],
            'from' => Carbon::CreateFromFormat('H:i', $request->data['from']),
            'to' => Carbon::CreateFromFormat('H:i', $request->data['to']),
            'days' => json_encode($request->data['days']),
            'specialist' => $user->id
        ]);

        return $specialtimes;
    }

    public function getspecialtimes(Request $request)
    {
        $user = Auth::user();

        $times = Special_availabilities::where('specialist', $user->id)->with(relations: ['specialist', 'service'])->get();
        $times->map(function ($item) {
            $item->days = json_decode($item->days, true); // Pārvērš JSON atpakaļ masīvā
            return $item;
        });
        return $times;
    }

}
