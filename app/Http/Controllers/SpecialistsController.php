<?php

namespace App\Http\Controllers;

use DB;
use App\Models\User;
use Illuminate\Http\Request;

class SpecialistsController extends Controller
{
    public function getspecialists(Request $request)
    {
        $specialists = User::where(['scope' => 'business', 'city' => $request->city])->get();
        return $specialists;
    }

    public function getspecialistbyname(Request $request)
    {
        $name = $request->name;
        $specialistbyname = User::where(['urlname' => $name])->get();

        return $specialistbyname;
    }

    public function getSpecialistsTimes(Request $request)
    {
        $user = User::where('id', $request->userid)->first();
        $settings = $user->settings;
        info($settings);
        return $settings;
    }
}
