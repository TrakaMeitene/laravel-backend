<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;


class AuthController extends Controller
{
    public function register(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'Email' => 'email|required| unique:users',
            'Password' => 'required',
            'Name' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'message' => 'Nepareizs e-pasts!',
            ];
        }

        $user = User::create([
            'name' => $request->input('Name'),
            'email' => $request->input('Email'),
            'password' => bcrypt($request->input('Password')),
            'scope' => $request->input('scope'),
            'urlname' => $request->input('urlname'),
            'personalnr' => $request->input('personalnr'),
            'bank' => $request->input('bank')
        ]);


        $token = $user->createToken($request->Name);
        if ($request->input('scope') == 'business') {
            $mailtrap = MailtrapClient::initSendingEmails(
                apiKey: getenv('MAILTRAP_API_KEY') # your API token from here https://mailtrap.io/api-tokens
            );

            $email = (new MailtrapEmail())
                ->from(new Address('info@pierakstspie.lv', 'Sveicināti Pieraksts Pie sistēmā!'))
                ->to(new Address($request->input('Email')))
                ->templateUuid('bba83ed9-bf46-4a37-aaa8-6070fb57c842')
                ->templateVariables([
                    'company_info_name' => 'Sandra Jurberga-Šaudine',
                    'company_info_city' => 'Liepaja',
                    'company_info_country' => 'Latvija',
                ])
            ;

            $mailtrap->send($email);
        }

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    public function logins(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
            'password' => 'required',
        ], [
            'email.exists' => 'The provided email does not match our records.',
        ]);

        if ($validator->fails()) {
            return ['message' => 'Nepareizs e-pasts!'];
        }

        $user = User::where(['email' => $request->email, 'scope' => $request->scope])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
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

        if ($request->email) {
            $client = Clients::where('userid', $user->id)->first();
            $client->update([
                'email' => $request->email
            ]);

        }

        return $user;
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function handleFacebookCallback()
    {
        try {

            $user = Socialite::driver('facebook')->stateless()->user();
            $finduser = User::where('email', $user->email)->first();

            if ($finduser) {
                Auth::login($finduser);
                $token = $finduser->createToken($user->email);


                User::where('email', $user->email)->update([
                    'facebook_id' => $user->id
                ]);

                return redirect()->intended('http://localhost:3000/');
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'facebook_id' => $user->id,
                    'password' => encrypt('123456dummy')
                ]);


                Auth::login($newUser);
                return redirect()->intended('http://localhost:3000/');
            }


        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }


    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $googleUser->email)->first();
        if (!$user) {
            $user = User::create(['name' => $googleUser->name, 'email' => $googleUser->email, 'password' => \Hash::make(rand(100000, 999999))]);
        }

        Auth::login($user);

        return redirect('http://localhost:3000/');
    }

    public function recoveremail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return ['status' => __("Esam nosūtījuši Jums e-pastā saiti!")];
        }

    }

    public function passwordreset(Request $request)
    {

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password2', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response([
                'status' => 'Parole nomainīta veiksmīgi!'
            ]);
        }

        return response([
            'status' => __($status)
        ]);

    }

    public function setonboardtime(Request $request)
    {
        $user = Auth::user();
        $user->update([
            'onboarded' => Carbon::now()->format('y-m-d h:i:s')
        ]);

        return ['onboarder' => Carbon::now()->format('y-m-d h:i:s')];

    }

    public function getonboardtime(Request $request)
    {
        $user = Auth::user();

        return response(['onboarder' => $user->onboarded]);

    }

}
