<?php

namespace App\Http\Controllers;
use App\Models\Service;
use Auth;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Invoice as Bill;
use Illuminate\Support\Facades\Mail;
use App\Mail\Invoice as InvoiceMail;

class InvoiceController extends Controller
{

    public static function generateSerial(): string
    {
        $date = Carbon::now()->format('Ymd'); // Current date
        $lastInvoice = Bill::where('external_customer', " ")->latest()->first();

        if ($lastInvoice) {
            // Increment last invoice serial number
            $lastSerial = intval(str_replace($date, '', $lastInvoice->serial_number));
            $newSerial = str_pad($lastSerial + 1, 4, '0', STR_PAD_LEFT);
        } else {
            // Start from 0001 if no invoices exist
            $newSerial = '0001';
        }

        return $newSerial;
    }

    public function makeinvoice(Request $request)
    {
        $specialist = User::where('id', $request->specialist)->first();
        //pro lietotājiem veido un sūta rēķinu, bezmaksas nē
        if ($specialist->subscription(env('STRIPE_PROD_ID'))) {
            $service = Service::where('id', $request->service)->first();
            $serialNumber = self::generateSerial();
            $customer = new Buyer([
                'name' => $request->title,
                'phone' => $request->phone,
                'custom_fields' => [
                    'Epasts' => $request->email,
                ],
            ]);

            $seller = new Party([
                'name' => $specialist->name,
                'address' => $specialist->address,
                'code' => $specialist->personalnr,
                'custom_fields' => [
                    'Konta numurs' => $specialist->bank
                ],
            ]);

            $item = InvoiceItem::make($service->name)->pricePerUnit($service->price / 100);

            $invoice = Invoice::make("Rēķins")
                ->series($serialNumber)
                ->buyer($customer)
                ->seller($seller)
                ->addItem($item)
                ->filename($serialNumber)
                ->save('public');

            $link = $invoice->url();

            Bill::Create([
                'user' => $specialist->id,
                'invoice' => $link,
                'status' => 'unpaid',
                'paid_date' => null,
                'customer' => is_numeric($request->user) ? $request->user : collect($request->user)->only(['id'])->get('id'),
                'serial_number' => $serialNumber,
                'service' => $service->name,
                'price' => $service->price,
                'external_customer' => " ",
                'booking' => $request->booking
            ]);


            Mail::to($request->email)->send(new InvoiceMail($serialNumber));


            return $link;
        }
    }

    public function getCustomerInvoices(Request $request)
    {
        $page = $request->current;
        $user = Auth::user();

        $query = Bill::where('customer', $user->id)->with(['specialist'])->orderBy('created_at', 'desc');

        if (
            $request->month &&
            ($request->prevmonth != $request->month || $request->prevStatus != $request->status)
        ) {
            $page = 1;
        }

        if ($request->month) {
            $query->whereBetween('created_at', [
                Carbon::createFromDate(2024, $request->month, 1, 'Europe/Riga'),
                Carbon::createFromDate(2024, $request->month, 31, 'Europe/Riga'),
            ]);
        }

        if ($request->status != "Visi") {
            $query->where('status', match ($request->status) {
                "Apmaksāts" => "paid",
                "Neapmaksāts" => "unpaid",
                "Anulēts" => "cancelled",
                default => "*", // Handle unexpected values gracefully
            });
        }

        $invoices = $query->paginate(4, ['*'], 'page', $page);



        return $invoices;
    }



    public function getSpecialistinvoices(Request $request)
    {
        $page = $request->current;
        $user = Auth::user();

        $query = Bill::where('user', $user->id)->with(['customer'])->orderBy('created_at', 'desc');
        if (
            $request->month &&
            ($request->prevmonth != $request->month || $request->prevType != $request->type || $request->prevStatus != $request->status)
        ) {
            $page = 1;
        }

        if ($request->selectedyear) {
            $query->whereBetween('created_at', [
                Carbon::createFromDate($request->selectedyear ? (int) $request->selectedyear : Carbon::now()->format('Y'), 1, 1, 'Europe/Riga'),
                Carbon::createFromDate($request->selectedyear ? (int) $request->selectedyear : Carbon::now()->format('Y'), 12, 31, 'Europe/Riga'),

            ]);

        }


        // Month filter
        if ($request->month) {
            $query->whereBetween('created_at', [
                Carbon::createFromDate($request->selctedyear ? (int) $request->selectedyear : Carbon::now()->format('Y'), $request->month, 1, 'Europe/Riga'),
                Carbon::createFromDate($request->selectedyear ? (int) $request->selectedyear : Carbon::now()->format('Y'), $request->month, 31, 'Europe/Riga'),
            ]);
        }


        if ($request->type != "Ieņēmumi/izdevumi") {
            $query->where('type', match ($request->type) {
                "Izdevumi" => "expenses",
                'Ieņēmumi' => "income",
                default => null
            });
        }

        if ($request->status != "Visi") {
            $query->where('status', match ($request->status) {
                "Apmaksāts" => "paid",
                "Neapmaksāts" => "unpaid",
                "Anulēts" => "cancelled",
                default => null // Handle unexpected values gracefully
            });
        }

        $invoices = $query->paginate(4, ['*'], 'page', $page);

        return $invoices;
    }

    public function updateInvoice(Request $request)
    {
        $user = Auth::user();
        Bill::find($request->id)->update([
            'paid_date' => now(),
            'status' => "paid"
        ]);

        $invoices = $user->specialistInvoices;

        return response([
            'status' => 'Dati nomainīti veiksmīgi!'
        ]);

    }

    public function getsumm(Request $request)
    {
        $user = Auth::user();
        $invoices = $user->specialistInvoices;
        $total = collect($invoices)->where('type', "income")->sum('price') / 100;
        $unpaid = collect($invoices)->where('paid_date', null)->sum('price') / 100;
        $data = collect();

        $thismonthSum = collect($invoices)->whereBetween(
            'created_at',
            [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]
        )->sum('price') / 100;

        $expenses = collect($invoices)->where('type', 'expenses')->sum('price') / 100;
        $thismonthExpenses = collect($invoices)->whereBetween(
            'created_at',
            [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]
        )->where('type', 'expenses')->sum('price') / 100;

        $data->push(
            [
                'total' => $total,
                'unpaid' => $unpaid,
                'thismonth' => $thismonthSum,
                'expenses' => $expenses,
                'thismonthexpenses' => $thismonthExpenses
            ]

        );
        return $data;
    }

    public function saveexternalinvoice(Request $request)
    {
        $user = Auth::user();
        $path = "";

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('', 'public');

        }
        $sum = $request->sumofbill * 100;
        $invoice = Bill::Create([
            'user' => $user->id,
            'invoice' => $path,
            'created_at' => Carbon::parse($request->date, 'Europe/Riga'),
            'status' => $request->paydate ? "paid" : 'unpaid',
            'paid_date' => $request->paydate ? Carbon::parse($request->paydate, 'Europe/Riga') : null,
            'customer' => 0,
            'external_customer' => $request->customer,
            'serial_number' => $request->documentNr,
            'service' => $request->service == null ? "" : $request->service,
            'price' => $sum,
            'type' => "expenses"

        ]);
        return $invoice;

    }

    public function Getyearsofbills(Request $request)
    {
        $user = Auth::user();
        $invoices = $user->specialistInvoices;
        $years = collect();
        foreach ($invoices as $key => $value) {
            $years->push(
                (int) Carbon::parse($value->created_at)->format('Y')
            );
        }
        return $years->unique()->flatten();

    }
}
