<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Sub Plan.
     */
    public function subscribe(Request $request)
    {
        $plan = Plan::findOrFail($request->plan_id);
    
        $response = Http::withToken(config('services.paystack.secret'))
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => auth()->user()->email,
                'amount' => $plan->amount,
                'plan' => $plan->paystack_plan_code,
                'callback_url' => route('payment.callback'),
            ]);
    
        return redirect($response['data']['authorization_url']);
    }

    public function callback(Request $request)
    {
        $reference = $request->reference;
    
        $response = Http::withToken(config('services.paystack.secret'))
            ->get("https://api.paystack.co/transaction/verify/{$reference}");
    
        $data = $response['data'];

        $user = Auth::user();

    
        if ($data['status'] === 'success') {
    
            $planCode = $data['plan']['plan_code'];
    
            $plan = Plan::where('paystack_plan_code', $planCode)->first();
    
            Subscription::updateOrCreate(
                ['user_id' => auth()->id()],
                [
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'paystack_subscription_code' => $data['subscription'],
                    'next_payment_date' => now()->addMonth()
                ]
            );
        }
    
        return redirect('/dashboard');
    }



    public function handle(Request $request)
{
    $event = $request->event;
    $data = $request->data;

    if ($event === 'invoice.payment_failed') {

        Subscription::where('paystack_subscription_code', $data['subscription']['subscription_code'])
            ->update(['status' => 'inactive']);
    }

    if ($event === 'charge.success') {

        Subscription::where('paystack_subscription_code', $data['subscription']['subscription_code'])
            ->update([
                'status' => 'active',
                'next_payment_date' => now()->addMonth()
            ]);
    }

    return response()->json(['status' => 'ok']);
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
    public function show(Subscription $subscription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        //
    }
}
