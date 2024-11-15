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
            'external_customer' => " "
        ]);


        Mail::to($request->email)->send(new InvoiceMail($serialNumber));


        return $link;
    }

    public function getCustomerInvoices(Request $request)
    {
        $user = Auth::user();
        $res = collect();

        //  $invoices = $user->customerInvoices->load('specialist')->sortBy('created_at', SORT_REGULAR, 'desc');
        $page = $request->current;
        $invoices = Bill::with(relations: ['specialist'])->where('customer', $user->id)->paginate(4, ['*'], 'page', $page);

        return $invoices;
    }

    public function getSpecialistInvoices(Request $request)
    {

        $user = Auth::user();
        $page = $request->current;
        if ($request->month) {
            $invoices = Bill::with(relations: ['customer'])
                ->whereBetween(
                    'created_at',
                    [
                        Carbon::createFromDate(2024, $request->month, 1, 'Europe/Riga'),
                        Carbon::createFromDate(2024, $request->month, 31, 'Europe/Riga')
                    ]
                )
                ->orderBy('created_at', 'desc')
                ->paginate(4, ['*'], 'page', $page);
        } else {
            $invoices = Bill::with(relations: ['customer'])
                ->orderBy('created_at', 'desc')
                ->paginate(4, ['*'], 'page', $page);
        }

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
        return $invoices;

    }

    public function getsumm(Request $request)
    {
        $user = Auth::user();
        $invoices = $user->specialistInvoices;
        $total = collect(value: $invoices)->sum('price') / 100;
        $unpaid = collect($invoices)->where('paid_date', null)->sum('price') / 100;
        $data = collect();

        $thismonthSum = collect($invoices)->whereBetween(
            'created_at',
            [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]
        )->sum('price') / 100;

        $data->push(
            [
                'total' => $total,
                'unpaid' => $unpaid,
                'thismonth' => $thismonthSum

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
        $sum = $request->sumofbill;
        $invoice = Bill::Create([
            'user' => $user->id,
            'invoice' => $path,
            'created_at' => Carbon::parse($request->date, 'Europe/Riga'),
            'status' => $request->paydate ? "paid" : 'unpaid',
            'paid_date' => $request->paydate ? Carbon::parse($request->paydate, 'Europe/Riga') : null,
            'customer' => 0,
            'external_customer' => $request->customer,
            'serial_number' => $request->documentNr,
            'service' => "",
            'price' => $sum

        ]);
        return $invoice;

    }
}
