<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Servicecontroller extends Controller
{
    public function addservice(Request $request)
    {
        $userDetails = Auth::user();  // To get the logged-in user details
        $user = User::find($userDetails->id);

        $service = Service::updateOrCreate([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'time' => $request->input('time'),
            'description' => $request->input('description'),
            'user' => $user->id,
        ]);
        return $service;
    }

    public function getservices(Request $request)
    {
        $userDetails = Auth::user();  // To get the logged-in user details
        $user = User::find($userDetails->id);

        $services = DB::table('services')->where('user', $userDetails->id)->get();
        return $services;
    }

    public function getservicebyid(Request $request)
    {
        $userDetails = Auth::user();
        $service = $userDetails->services->where('id', $request->service)->first();

        return $service;
    }

    public function getservicesforspecialist(Request $request)
    {
        $userid = $request->id;
        $user = User::find($userid);
        $services = $user->services;
        return $services;
    }


}
