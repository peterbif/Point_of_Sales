<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function searchOrders(Request $request)
    {
        //

        $request->validate([
            'transaction_id' => 'required|string',
            
        ]);


        $transactionId = $request->transaction_id;

        $query = Order::with('order_details', 'user', 'order_details.product', 'order_details.product.category')->where('orders.transaction_id', 'like', "%{$transactionId}%");
    
          $orders = $query
            ->orderBy('id', 'DESC')->get();
        
    
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);

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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    

 
public function deleteOrderDetail($id)
{
    DB::transaction(function () use ($id) {

        // 1. Find and delete the order detail
        $orderDetail = OrderDetail::findOrFail($id);
        $transaction_id = $orderDetail->transaction_id;
        $orderDetail->delete();

        // 2. Get parent order
        $order = Order::where('transaction_id', $transaction_id)->firstOrFail();

        // 3. Get remaining order details
        $details = $order->order_details()->get();

        $total = 0;
        $totalDiscount = 0;

        foreach ($details as $detail) {
            $lineTotal = $detail->unit_price * $detail->qty;
            $lineDiscount = $lineTotal * ($detail->discount / 100); // per-item discount

            $total += $lineTotal;
            $totalDiscount += $lineDiscount;
        }

        // 4. Update order totals
        $order->total = $total; // subtotal
        $order->total_discount = $totalDiscount; // total discount in amount

        // 5. Update order discount percentage dynamically
        $order->discount = $total > 0 ? ($totalDiscount / $total) * 100 : 0;

        // 6. Update grand total
        $order->grand_total = $total - $totalDiscount;

        $order->save();
    });

    return response()->json([
        'status' => 'success',
        'message' => 'Order detail deleted and order totals updated.'
    ]);
}
          
}
