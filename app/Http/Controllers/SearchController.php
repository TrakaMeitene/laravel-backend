<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Clients;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class SearchController extends Controller
{
    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('searchvalue');
        $clients = Clients::where('specialist', $user->id)->where('name', 'like', value: "%$search%")->orWhere('surname', 'like', "%$search%")->orWhere('email', 'like', "%$search%")->get();
        $invoices = Invoice::where('user', $user->id)->with(relations: ['customer'])->where('serial_number', 'like', value: "%$search%")->get();

        $response = collect();
        $response->push([
            'clients' => $clients,
            'invoices' => $invoices
        ]);
        return $response;
    }
}
