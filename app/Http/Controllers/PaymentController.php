<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;

class PaymentController extends Controller
{
    public function stripesession(Request $request)
    {
        $user = Auth::user();
         $stripePriceId = env('STRIPE_PRICE_ID');


        $quantity = 1;
        $front = env('FRONTEND_URL');

        return $user
            ->newSubscription(env('STRIPE_PROD_ID'), $stripePriceId)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('success-route', ['user' => $user]),
                'cancel_url' => route('fail-route'),
            ])->toArray();

    }

    public function success(Request $request)
    {
        //te jāieseivo ka ir useris subscription lietotājs
        //jāreturno uz skatu /admin/sucesspayment
        $stripePriceId = env('STRIPE_PRICE_ID');

        User::find($request->user)->update(['abonament' => 'business']);

        return redirect(env('FRONTEND_URL') . '/admin/sucesspayment');
    }

    public function fail(Request $request)
    {
        $user = Auth::user();

        return redirect(env('FRONTEND_URL') . '/admin/failedpayment');
    }

 
}
