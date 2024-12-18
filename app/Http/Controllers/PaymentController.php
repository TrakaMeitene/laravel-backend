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
        // $stripePriceId = 'price_1QVUsn2K3ttu5uf5yTBUHB55';
        $stripePriceId = 'price_1QVyfs2K3ttu5uf5PimEXey3';


        $quantity = 1;
        $front = env('FRONTEND_URL');
            // ->newSubscription("prod_ROHRSQBHhWHvLv", $stripePriceId)

        return $user
            ->newSubscription('prod_ROmEFILN29hPqt', $stripePriceId)
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
        $stripePriceId = 'price_1QVyfs2K3ttu5uf5PimEXey3';

        User::find($request->user)->update(['abonament' => 'business']);

        return redirect(env('FRONTEND_URL') . '/admin/sucesspayment');
    }

    public function fail(Request $request)
    {
        $user = Auth::user();

        return redirect(env('FRONTEND_URL') . '/admin/failedpayment');
    }

    public function clientportal(Request $request)
    {
        $user = Auth::user();
        return redirect('https://billing.stripe.com/p/login/aEUfZ3bLf5Dpcp2144');

       // info($user->subscriptions->where('type', 'prod_ROmEFILN29hPqt'));
     
    }
}
