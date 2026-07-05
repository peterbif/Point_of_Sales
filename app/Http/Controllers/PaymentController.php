<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    /**
     * Initiase payment.
     */
   
     public function initializePayment(Request $request)
{
    $response = Http::withToken(config('services.paystack.secret'))
        ->post('https://api.paystack.co/transaction/initialize', [
            'email' => $request->email,
            'amount' => $request->amount * 100, // kobo
            'callback_url' => route('payment.callback'),
        ]);

    return redirect($response['data']['authorization_url']);
}


public function verifyPayment($reference)
{
    $response = Http::withToken(config('services.paystack.secret'))
        ->get("https://api.paystack.co/transaction/verify/{$reference}");

    if ($response['data']['status'] === 'success') {
        // ✅ Activate subscription / order
    }
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }
}
