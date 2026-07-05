<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    public function createPlan(Request $request)
    {
        $response = Http::withToken(config('services.paystack.secret'))
            ->post('https://api.paystack.co/plan', [
                'name' => $request->name,
                'interval' => $request->interval, // monthly
                'amount' => $request->amount
            ]);
    
        $planCode = $response['data']['plan_code'];
    
        Plan::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'interval' => $request->interval,
            'paystack_plan_code' => $planCode
        ]);
    
        return response()->json(['success' => true]);
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
    public function show(Plan $plan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        //
    }
}
