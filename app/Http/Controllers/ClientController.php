<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\Clients;
class ClientController extends Controller
{
    public function saveclient(Request $request)
    {
        $user = Auth::user();

        Clients::Create([
            'name'=> $request->name,
            'surname'=> $request->surname,
            'phone'=> $request->phone,
            'email'=> $request->email,
            'specialist'=> $user->id,
            'userid' => null

        ]);

    }

    public function getclients(Request $request)
    {

        $user = Auth::user();
        $clients = $user->clients;
        return $clients;
    }
}
