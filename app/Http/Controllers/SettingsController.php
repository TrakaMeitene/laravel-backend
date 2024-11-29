<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

}
