<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $request->validate([
            'Email' => 'email|required',
            'Password' => 'required',
            'Name' => 'required',
        ]);

        $user = User::create([
            'name' => $request->input('Name'),
            'email' => $request->input('Email'),
            'password' => bcrypt($request->input('Password')),
            'scope' => $request->input('scope'),
        ]);

        $token = $user->createToken($request->Name);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    public function logins(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required',
        ]);
        $user = User::where(['email' => $request->email, 'scope' => $request->scope])->first();

        if (!$user || Hash::check($request->password, $user->password)) {
            return [
                'message' => 'Nepareiza parole un/vai e-pasts!',
            ];
        }

        $token = $user->createToken($user->email);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return "success";
    }

    public function user(Request $request)
    {
        $user = $request->user();

        return $user;
    }

    public function updateuser(Request $request)
    {
        $userDetails = Auth::user();  // To get the logged-in user details
        $user = User::find($userDetails->id);
                 if ($request->hasFile('file')) {

                     $path = $request->file('file')->store('images', 'public');


                    $user->avatar = $path;
                }
        $user->update($request->all());
        $user->save();

        return $user;
    }
}
