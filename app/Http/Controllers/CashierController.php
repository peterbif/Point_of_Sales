<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderDetailTemp;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Table;
use App\Models\Vat;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CashierController extends Controller
{
    // public function index(Request $request)
    // {

    //     try {

    //         $search = trim($request->search);
    
    //         // Prevent heavy queries
    //         if (!$search || strlen($search) < 2) {
    //             return response()->json([
    //                 'success' => true,
    //                 'products' => []
    //             ]);
    //         }
    
    //         $products = Product::select('id', 'name', 'unit_price', 'inventory', 'image')
    //             // ->where('name', 'LIKE', "%{$search}%")
    //             ->where(function($q) use ($search) {
    //                 $q->where('name', 'LIKE', "%{$search}%")
    //                   ->orWhere('barcode', $search);
    //             })
    //             ->orderBy('name', 'ASC')
    //             ->limit(20) // VERY IMPORTANT for search performance
    //             ->get();
    
    //         return response()->json([
    //             'success' => true,
    //             'products' => $products
    //         ]);
    
    //     } catch (\Throwable $ex) {
    
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Server error'
    //         ], 500);
    //     }
    // }

   
   
   
    public function index(Request $request)
{
    try {

        $search = trim($request->search);
        $search = preg_replace('/\s+/', '', $search); // remove ALL spaces

        // if (!$search || strlen($search) < 2) {
        //     return response()->json([
        //         'success' => true,
        //         'products' => []
        //     ]);
        // }

        // // 🔥 1. Try barcode FIRST (fast, indexed)
        // $barcodeMatch = Product::select('id', 'name', 'unit_price', 'inventory', 'image')
        //     ->where('barcode', $search)
        //     ->first();

        // if ($barcodeMatch) {
        //     return response()->json([
        //         'success' => true,
        //         'products' => [$barcodeMatch] // single result
        //     ]);
        // }

        // // 🔥 2. Fallback to name search (optimized)
        // $products = Product::select('id', 'name', 'unit_price', 'inventory', 'image')
        //     ->where('name', 'LIKE', "%{$search}%") // ✅ index-friendly
        //     ->orderBy('name', 'ASC')
        //     ->limit(20)
        //     ->get();

     $products = Product::select('id', 'name', 'unit_price', 'wholesales_price', 'inventory', 'image')
    ->where(function ($q) use ($search) {

        // If numeric → likely barcode
        if (ctype_digit($search)) {
            $q->orWhere('barcode', $search);
        }

        // Always allow name search
        $q->orWhere('name', 'LIKE', "%{$search}%");
    })
    ->orderByRaw("
        CASE 
            WHEN barcode = ? THEN 1
            WHEN name LIKE ? THEN 2
            ELSE 3
        END
    ", [$search, "%{$search}%"])
    ->limit(20)
    ->get();

        return response()->json([
            'success' => true,
            'products' => $products
        ]);

    } catch (\Throwable $ex) {

        return response()->json([
            'success' => false,
            'message' => 'Server error'
        ], 500);
    }
}
   
   
   
   
    public function showTable($id = 0)
    {
        try {
            $data = Table::select('id', 'name', 'status')->where('id', '!=', $id)->orderBy('name')->get();
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        return response()->json(['success' => true, 'data' => $data]);
    }


    // private function getOrder($transaction_id): JsonResponse{
    

    //     $data = OrderDetailTemp::where("transaction_id", '=',$transaction_id)->get();
    //     if ($data && $data->count() > 0) {
    //         $grand_total = $data->sum(function ($detail) {
    //             return $detail->qty * $detail->unit_price;
    //         });

    //         $total = $data->sum(function ($detail) {
    //             return $detail->qty * $detail->unit_price * (1 - $detail->discount / 100);
    //         });
    //         $total_discount = $grand_total - $total + ($total * $data->discount / 100);
    //         $data->grand_total = $grand_total;
    //         $data->total = $total;
    //         $data->total_discount = $total_discount;
    //         $data->net_amount = $grand_total - $total_discount;
    //         $data->save();
    //     }
    //     return response()->json(['success' => true, 'data' => $data]);
    // }



    private function  getOrder(string $transaction_id): JsonResponse
{
    $details = OrderDetailTemp::with("product", "user")->where('transaction_id', $transaction_id)->get();

    if ($details->isEmpty()) {
        return response()->json([
            'success' => true,
            'data' => [],
            'summary' => [
                'grand_total' => 0,
                'total_discount' => 0,
                'net_amount' => 0
            ]
        ]);
    }

    // Sum before discount
    $grandTotal = $details->sum(function ($item) {
        return $item->qty * $item->unit_price;
    });

    // Sum after line discount
    $netAmount = $details->sum(function ($item) {
        $lineTotal = $item->qty * $item->unit_price;
        $lineDiscount = $lineTotal * ($item->discount / 100);
        return $lineTotal - $lineDiscount;
    });

    // $totalDiscount = $grandTotal - $netAmount;

    return response()->json([
        'success' => true,
        'data' => $details,
        // 'summary' => [
        //     'grand_total' => round($grandTotal, 2),
        //     'total_discount' => round($totalDiscount, 2),
        //     'net_amount' => round($netAmount, 2),
        // ]
    ]);
}

    // // public function selectTable(Request $request)
    // // {
    // //     $selectedItem = json_decode($request->ids);
    // //     try {
    // //         DB::beginTransaction();
    // //         if ($request->old_transaction_id > 0 && count($selectedItem) > 0) {
    // //             $old_table = Table::with('order_detail_temps')->find($request->old_transaction_id);
    // //             Table::where('id', $request->new_transaction_id)->update(['status' => 1, 'discount' => $old_table->discount]);
    // //             if ($old_table && $old_table->order_detail_temps()->count() == 0) {
    // //                 $old_table->status = 2;
    // //                 $old_table->discount = 0;
    // //                 $old_table->total_discount = 0;
    // //                 $old_table->grand_total = 0;
    // //                 $old_table->total = 0;
    // //                 $old_table->net_amount = 0;
    // //             } else {
    // //                 $old_table->order_detail_temps()->whereIn('id', $selectedItem)->update(['transaction_id' => $request->new_transaction_id]);
    // //                 $grand_total = $old_table->order_detail_temps->whereNotIn('id', $selectedItem)->sum(function ($detail) {
    // //                     return $detail->qty * $detail->unit_price;
    // //                 });
    // //                 $total = $old_table->order_detail_temps->whereNotIn('id', $selectedItem)->sum(function ($detail) {
    // //                     return $detail->qty * $detail->unit_price * (1 - $detail->discount / 100);
    // //                 });
    // //                 $total_discount = $grand_total - $total + ($total * $old_table->discount / 100);
    // //                 $old_table->grand_total = $grand_total;
    // //                 $old_table->total = $total;
    // //                 $old_table->total_discount = $total_discount;
    // //                 $old_table->net_amount = $grand_total - $total_discount;
    // //             }
    // //             $old_table->save();
    // //         }
    // //         DB::commit();
    // //     } catch (Exception $ex) {
    // //         DB::rollBack();
    // //         abort($ex->getCode());
    // //     }
    // //     return $this->getOrder($request->transaction_id);
    // // }

    // public function addToOrder(Request $request)
    // {

    //      // return $request->all();

    //     //$transactionId = 'ORD-' . now()->format('YmdHis') . '-' . rand(100,999);

    //     $product = Product::find($request->product_id);
    //     try {
    //         DB::beginTransaction();
    //         $order_detail = OrderDetailTemp::where('product_id', $product->id)->first();
    //         if (!$order_detail) {
    //             $order_detail = new OrderDetailTemp();
    //             $order_detail->transaction_id = $request->transactionId;
    //             $order_detail->product_id = $product->id;
    //             $order_detail->product_category_id = $product->product_category_id;
    //             $order_detail->qty = 1;
    //             $order_detail->description = $product->name;
    //             $order_detail->unit_price = $product->unit_price;
    //             $order_detail->created_by_id = $request->user()->id;
    //         } else {
    //             $order_detail->qty += 1;
    //         }
    //         $order_detail->updated_by_id = $request->user()->id;
    //       //  $order_detail->table->status = 1;
    //         $order_detail->push();
    //         DB::commit();
    //     } catch (Exception $ex) {
    //         dd($ex->getMessage());
    //         DB::rollBack();
    //         abort($ex->getCode(), $ex->getMessage());
    //     }
    //     return $this->getOrder($request->transactionId);
    // }

//     public function addToOrder(Request $request): JsonResponse{

//     //return  $request->all();


//     $request->validate([
//         'product_id' => 'required|exists:products,id',
//         'transactionId' => 'required|string'
//     ]);

//     try {
//         DB::beginTransaction();

//         $product = Product::findOrFail($request->product_id);

//         // VERY IMPORTANT: include transaction_id
//         $orderDetail = OrderDetailTemp::where('transaction_id', $request->transactionId)
//             ->where('product_id', $product->id)
//             ->where('created_by_id', $request->user()->id)
//             ->first();

//         if (!$orderDetail) {

//             $orderDetail = OrderDetailTemp::create([
//                 'transaction_id'      => $request->transactionId,
//                 'product_id'          => $product->id,
//                 'product_category_id' => $product->product_category_id,
//                 'qty'                 => 1,
//                 'description'         => $product->name,
//                 'unit_price'          => $product->unit_price,
//                 'discount'            => 0,
//                 'created_by_id'       => $request->user()->id,
//                 'updated_by_id'       => $request->user()->id,
//             ]);

//         } else {

//             $orderDetail->increment('qty');
//             $orderDetail->update([
//                 'updated_by_id' => $request->user()->id
//             ]);
//         }

//         DB::commit();

//     } catch (\Throwable $e) {

//         DB::rollBack();

//         return response()->json([
//             'success' => false,
//             'message' => $e->getMessage()
//         ], 500);
//     }

//     return $this->getOrder($request->transactionId);
// }


public function addToOrder(Request $request)
{

//    return $request->all();


    $request->validate([
        'product_id' => 'required|exists:products,id',
        'transactionId' => 'required|string'
    ]);

    try {
        DB::beginTransaction();

        $product = Product::findOrFail($request->product_id);

       // return $product->inventory;


            // 1. Expiry check
        if ($product->expiry_date && Carbon::parse($product->expiry_date)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => "{$product->name} has expired and cannot be dispensed."
            ]);
        }

        // check existing order detail for this product and transaction
        $orderDetail = OrderDetailTemp::where('transaction_id', $request->transactionId)
            ->where('product_id', $product->id)
            ->where('created_by_id', $request->user()->id)
            ->first();

        $currentQty = $orderDetail ? $orderDetail->qty : 0;

        // check stock
        if ($currentQty + 1 > $product->inventory) {
            return response()->json([
                'success' => false,
                'message' => "Not enough stock for {$product->name}"
            ]);
        }

        if (!$orderDetail) {
            // create new order detail

            if($request->useWholesalePrice){
                $price =  $product->wholesales_price;
            }else{

                $price =  $product->unit_price;

            }
            
            $orderDetail = OrderDetailTemp::create([
                'transaction_id'      => $request->transactionId,
                'product_id'          => $product->id,
                'product_category_id' => $product->product_category_id,
                'qty'                 => 1,
                'description'         => $product->name,
                'unit_price'          => $price,
                'discount'            => 0,
                'created_by_id'       => $request->user()->id,
                'updated_by_id'       => $request->user()->id,
            ]);
        } else {
            // increment qty
            $orderDetail->increment('qty');
            $orderDetail->update([
                'updated_by_id' => $request->user()->id
            ]);
        }

        DB::commit();

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }

    return $this->getOrder($request->transactionId);
}



    public function deleteOrder($product_id, $transaction_id)
    {
        try {
            OrderDetailTemp::destroy($product_id);
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        return $this->getOrder($transaction_id);
    }

    public function updateOrderQty(Request $request)
    {
        try {
            $data = OrderDetailTemp::find($request->id);
            $data->qty = $request->qty;
            $data->save();
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        return $this->getOrder($request->transaction_id);
    }

    public function updateDetailDiscount(Request $request)
    {
        try {
            $data = OrderDetailTemp::find($request->id);
            $data->discount = $request->discount;
            $data->save();
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }

        
        return $this->getOrder($request->transaction_id);
    }
    

    public function updateOrderDiscount(Request $request)
{

    //return $request->all();


    $request->validate([
        'transaction_id' => 'required',
        'discount' => 'required|numeric|min:0'
    ]);

    try {

        OrderDetailTemp::where('transaction_id', $request->transaction_id)
            ->update([
                'discount' => $request->discount
            ]);

       
    } catch (\Exception $ex) {

        return response()->json([
            'success' => false,
            'message' => $ex->getMessage()
        ], 500);

    }

    return $this->getOrder($request->transaction_id);
}
    

    public function printInvoice(Request $request)
    {
        try {
            $data = Table::with(['order_detail_temps', 'operator'])->find($request->transaction_id);
            if (!$data->invoice_no)
                $data->invoice_no = date('YmdHis');
            $data->status = 3;
            $data->save();
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function completedOrders(Request $request){

        $userId = $request->user()->id;
    
        $orderDetails = Order::with("order_details")->where('created_by_id', $userId)
        ->limit(10)
        ->orderBy('id', 'DESC')
        ->get();

    
        if ($orderDetails->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Order is empty'
            ]);
        }


        return response()->json([
            'success' => true,
            'data' => $orderDetails
        ]);




     }

   


    

    // public function confirmPayment(Request $request)
    // {
    //     // $table = Table::find($request->transaction_id);

    //     $order_details = OrderDetailTemp::where("transaction_id",$request->transaction_id)->get();

    //     // validation
    //     $rules = [
    //         'receive_amount' => 'required|numeric|min:' . $request->grand_total,
    //     ];
    //     $validator = Validator::make($request->all(), $rules, [
    //         'receive_amount.required' => 'is required',
    //         'receive_amount.numeric' => 'must be number',
    //         'receive_amount.min' => 'must be at least ' . $request->grand_total,
    //     ]);
    //     if ($validator->fails())
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $validator->errors()
    //         ]);

    //     try {
    //         DB::beginTransaction();
    //         $order = new Order();
    //         $order->transaction_id = $request->transaction_id;
    //         $order->discount = $request->discount;
    //         $order->total_discount = $request->total_discount;
    //         $order->total = $request->total;
    //         $order->grand_total = $request->grand_total;
    //         $order->net_amount = $request->net_amount;
    //         $order->receive_amount = $request->receive_amount;
    //         $order->created_by_id = $request->user()->id;
    //         $order->updated_by_id = $request->user()->id;
    //         if ($order->save()) {
    //             foreach ($order_details as $item) {
    //                 $order_detail = new OrderDetail();
    //                 $order_detail->order_id = $order->id;
    //                 $order_detail->product_id = $item->product_id;
    //                 $order_detail->description = $item->description;
    //                 $order_detail->qty = $item->qty;
    //                 $order_detail->unit_price = $item->unit_price;
    //                 $order_detail->product_category_id = $item->product_category_id;
    //                 $order_detail->discount = $item->discount;
    //                 $order_detail->created_by_id = $request->user()->id;
    //                 $order_detail->updated_by_id = $request->user()->id;
    //                 $order_detail->save();
    //             }
    //       OrderDetailTemp::where('transaction_id', $request->transaction_id)->delete();
             
    //         }
    //         DB::commit();
    //     } catch (Exception $ex) {
    //         DB::rollBack();
    //         abort($ex->getCode(), $ex->getMessage());
    //     }
    //     return response()->json([
    //         'success' => true,
    //         'data' => Order::with(['order_details',  'user'])->find($order->id)
    //     ]);
    // }



    public function addCompletedOrders(Request $request){


    //return $request->all();

        $userId = $request->user()->id;
    
        $orderDetails = OrderDetail::with(['order'])->where('order_id', $request->id)
        ->where('created_by_id', $userId)

        ->get();
    
        if ($orderDetails->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Order is empty'
            ]);
        }


    
        return response()->json([
            'success' => true,
            'data' => $orderDetails
        ]);
    }



    public function searchCompletedOrders(Request $request){

       // return $request->all();

        $userId = $request->user()->id;
    
        $orderDetails = Order::with("order_details")
         ->where('transaction_id', 'LIKE', '%'.$request->search.'%')
         ->where('created_by_id', $userId)
        ->limit(1)
        ->orderBy('id', 'DESC')
        ->get();

    
        if ($orderDetails->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Order is empty'
            ]);
        }


        return response()->json([
            'success' => true,
            'data' => $orderDetails
        ]);

    }


    // public function draftedOrders(Request $request){

    //     // return $request->all();
 
    //      $userId = $request->user()->id;
     
    //      $orderDetails = OrderDetailTemp::where('created_by_id', $userId)
    //      ->groupBy('transaction_id')
    //      ->orderBy('id', 'DESC')
    //      ->get();
 
     
    //      if ($orderDetails->isEmpty()) {
    //          return response()->json([
    //              'success' => false,
    //              'message' => 'Order is empty'
    //          ]);
    //      }
 
 
    //      return response()->json([
    //          'success' => true,
    //          'data' => $orderDetails
    //      ]);
 
    //  }
    public function draftedOrders(Request $request)
    {
        $userId = $request->user()->id;
    
        $orders = OrderDetailTemp::with('product')
            ->where('created_by_id', $userId)
            ->orderByDesc('id')
            ->get()
            ->groupBy('transaction_id')
            ->map(function ($items, $transactionId) {
    
                $items = $items->map(function ($item) {
                    $item->subtotal = ($item->qty * $item->unit_price) - ($item->discount ?? 0);
                    return $item;
                });
    
                return [
                    'transaction_id' => $transactionId,
                    'items' => $items->values(),
                    'grand_total' => $items->sum('subtotal')
                ];
            })
            ->values();
    
            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No drafted orders found',
                    'data' => []
                ], 404);
            }
    
        return response()->json([
            'success' => true,
            'message' => 'Drafted orders retrieved successfully',
            'data' => $orders
        ]);
    }

    

    public function confirmPayment(Request $request)
    {
        $userId = $request->user()->id;
    
        $orderDetails = OrderDetailTemp::where('transaction_id', $request->transaction_id)->get();

        $vat = Vat::select('id', 'vat')
        ->latest()
        ->first();

         $vat2 = $vat?->vat ?? 0;

    
        if ($orderDetails->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Order is empty'
            ]);
        }
    
        /*
        |--------------------------------------------------------------------------
        | CHECK PRODUCT STOCK
        |--------------------------------------------------------------------------
        */
    
        foreach ($orderDetails as $item) {
    
            $product = DB::table('products')->where('id', $item->product_id)->first();
    
            if (!$product || $product->inventory < $item->qty) {
                return response()->json([
                    'success' => false,
                    'message' => $item->description . ' is out of stock'
                ]);
            }
        }
    
        /*
        |--------------------------------------------------------------------------
        | CALCULATE TOTALS
        |--------------------------------------------------------------------------
        */
    
        $total = 0;
        $totalDiscount = 0;
    
        foreach ($orderDetails as $item) {
    
            $lineTotal = $item->qty * $item->unit_price;
    
            $lineDiscount = $lineTotal * ($item->discount / 100);
    
            $total += $lineTotal;
    
            $totalDiscount += $lineDiscount;
        }
    
             $grandTotal = $total - $totalDiscount;

             $subTotal = $grandTotal; // before tax

            $vatRate = $vat2; // already fetched

            // $vatAmount = $subTotal * ($vatRate / 100);

            // $finalTotal = $subTotal + $vatAmount;

            $vatAmount = round($subTotal * ($vat2 / 100), 2);
            $finalTotal = round($subTotal + $vatAmount, 2);
                
                    /*
        |--------------------------------------------------------------------------
        | VALIDATE PAYMENT
        |--------------------------------------------------------------------------
        */
    
        $validator = Validator::make($request->all(), [
            'receive_amount' => ['required', 'numeric', 'min:' . $finalTotal],
            'mode_of_payment' => ['required', 'string']
        ], [
            'receive_amount.required' => 'Receive amount is required',
            'receive_amount.numeric' => 'Receive amount must be a number',
            'receive_amount.min' => 'Receive amount must be at least ' . $finalTotal,
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }
    
        try {
    
            $order = DB::transaction(function () use ($request, $orderDetails, $userId, $total, $totalDiscount, $finalTotal, $subTotal, $vatAmount) {
    
                /*
                |--------------------------------------------------------------------------
                | CREATE ORDER
                |--------------------------------------------------------------------------
                */
    
                $order = Order::create([
                    'transaction_id' => $request->transaction_id,
                    'discount' => 0,
                    'total_discount' => $totalDiscount,
                    'total' => $total,
                    'sub_total' => $subTotal,
                    'vat_amount' => $vatAmount,
                    'grand_total' => $finalTotal,
                    'mode_of_payment' => $request->mode_of_payment,
                    'receive_amount' => $request->receive_amount,
                    'created_by_id' => $userId,
                    'updated_by_id' => $userId,
                ]);
    
                /*
                |--------------------------------------------------------------------------
                | INSERT ORDER DETAILS
                |--------------------------------------------------------------------------
                */
    
                $details = $orderDetails->map(function ($item) use ($order, $request, $userId) {
                    return [
                        'order_id' => $order->id,
                        'transaction_id' => $request->transaction_id,
                        'product_id' => $item->product_id,
                        'description' => $item->description,
                        'qty' => $item->qty,
                        'unit_price' => $item->unit_price,
                        'product_category_id' => $item->product_category_id,
                        'discount' => $item->discount,
                        'created_by_id' => $userId,
                        'updated_by_id' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });
    
                OrderDetail::insert($details->toArray());
    
                /*
                |--------------------------------------------------------------------------
                | REDUCE PRODUCT STOCK
                |--------------------------------------------------------------------------
                */
    
                foreach ($orderDetails as $item) {
                    DB::table('products')
                        ->where('id', $item->product_id)
                        ->decrement('inventory', $item->qty);
                }
    
                /*
                |--------------------------------------------------------------------------
                | CLEAR TEMP CART
                |--------------------------------------------------------------------------
                */
    
                OrderDetailTemp::where('transaction_id', $request->transaction_id)->delete();
    
                return $order;
            });
    
            /*
            |--------------------------------------------------------------------------
            | LOAD RELATIONS FOR RECEIPT
            |--------------------------------------------------------------------------
            */
    
            $order->load(['order_details.product', 'user']);
    
            return response()->json([
                'success' => true,
                'data' => $order,
                'sub_total' => $subTotal,
                'vat_amount' => $vatAmount,
                'grand_total' =>   $finalTotal,
                'vat' => $vat2,
            ]);
    
        } catch (\Throwable $ex) {
    
            return response()->json([
                'success' => false,
                'message' => 'Payment failed',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

}
