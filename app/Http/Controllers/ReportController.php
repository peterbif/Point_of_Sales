<?php

namespace App\Http\Controllers;

use App\Exports\ExportDataToExcel;
use App\Models\Order;
use App\Models\OrderDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;



class ReportController extends Controller
{
    // Sale Summary
    // public function saleSummary(Request $request)
    // {
    //     $from_date = null;
    //     $to_date = null;

    //     $product_id = $request->product_id;
    //     $category_id = $request->category_id;

    //     if ($request->from_date)
    //         $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
    //     if ($request->to_date)
    //         $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));

    //     try {

    //         if($category_id){

           
    //         $data = Order::join('order_details', 'orders.id', '=', 'order_details.order_id')
    //             ->join('product_categories', 'product_categories.id', '=', 'order_details.product_category_id')
    //             ->select(DB::raw("product_categories.name,sum((order_details.qty * order_details.unit_price*order_details.discount/100) + (order_details.qty * order_details.unit_price * (1-order_details.discount/100) * orders.discount/100)) as discount, sum(order_details.qty * order_details.unit_price) as total"))
    //             ->when($from_date, function ($query) use ($from_date) {
    //                 $query->where('orders.created_at', '>=', $from_date);
    //             })
    //             ->when($to_date, function ($query) use ($to_date) {
    //                 $query->where('orders.created_at', '<=', $to_date);
    //             })
    //             ->groupBy(DB::raw('product_categories.name'))
    //             ->orderBy('product_categories.name', 'DESC')
    //             ->get(); 
    //         }

    //         if($$product_id){
    //             $data = order_details::join('products', 'products.id', '=', 'order_details.product_id')
    //                 ->select(DB::raw("products.name,sum((order_details.qty * order_details.unit_price*order_details.discount/100) + (order_details.qty * order_details.unit_price * (1-order_details.discount/100) )) as discount, sum(order_details.qty * order_details.unit_price) as total"))
    //                 ->when($from_date, function ($query) use ($from_date) {
    //                     $query->where('orders.created_at', '>=', $from_date);
    //                 })
    //                 ->when($to_date, function ($query) use ($to_date) {
    //                     $query->where('orders.created_at', '<=', $to_date);
    //                 })
    //                 ->when($product_id, function ($query) use ($product_id) {
    //                     $query->where('order_details.product_id', '<=', $product_id);
    //                 })
    //                 ->groupBy(DB::raw('product_categories.name'))
    //                 ->orderBy('product_categories.name', 'DESC')
    //                 ->get(); 
    //             }


    //         $response['success'] = true;
    //         $response['data'] = $data;
    //     } catch (Exception $ex) {
    //         abort($ex->getCode(), $ex->getMessage());
    //     }
    //     // return back to compoment
    //     return response()->json($response);
    // }


    public function saleSummary(Request $request)
    {
        try {
            $fromDate   = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
            $toDate     = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;
            $productId  = $request->product_id;
            $categoryId = $request->category_id;
    
            $query = Order::query()
                ->join('order_details', 'orders.id', '=', 'order_details.order_id');
    
            /*
            |--------------------------------------------------------------------------
            | Apply Filters First (Clean approach)
            |--------------------------------------------------------------------------
            */
            if ($fromDate && $toDate) {
                $query->whereBetween('orders.created_at', [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $query->where('orders.created_at', '>=', $fromDate);
            } elseif ($toDate) {
                $query->where('orders.created_at', '<=', $toDate);
            }
    
            /*
            |--------------------------------------------------------------------------
            | Dynamic Join + Grouping
            |--------------------------------------------------------------------------
            */
            if ($categoryId) {
    
                $query->join('product_categories', 'product_categories.id', '=', 'order_details.product_category_id')
                    ->addSelect('product_categories.name as label')
                    ->where('order_details.product_category_id', $categoryId)
                    ->groupBy('product_categories.name');
    
            } else {
    
                $query->join('products', 'products.id', '=', 'order_details.product_id')
                    ->addSelect('products.name as label');
    
                if ($productId && $productId !== 'all') {
                    $query->where('order_details.product_id', $productId);
                }
    
                $query->groupBy('products.name');
            }
    
            /*
            |--------------------------------------------------------------------------
            | Common Aggregations (Single source of truth)
            |--------------------------------------------------------------------------
            */
            $query->addSelect([
                DB::raw("
                    SUM(
                        (order_details.qty * order_details.unit_price * order_details.discount / 100)
                        +
                        (order_details.qty * order_details.unit_price * (1 - order_details.discount / 100) * orders.discount / 100)
                    ) as discount
                "),
                DB::raw("SUM(order_details.qty * order_details.unit_price) as total"),
                DB::raw("SUM(order_details.qty) as inventory")
            ]);
    
            $data = $query
                ->orderBy('label', 'DESC')
                ->get();
    
            return response()->json([
                'success' => true,
                'data'    => $data
            ]);
    
        } catch (\Throwable $e) {
            report($e);
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales summary'
            ], 500);
        }
    }

    // public function productSummary(Request $request)
    // {
    //     // get param value
    //     $product_name = $request->product_name;
    //     $product_category_id = $request->product_category_id ?? 0;
    //     $from_date = null;
    //     $to_date = null;
    //     if ($request->from_date)
    //         $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
    //     if ($request->to_date)
    //         $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));
    //     $sortBy = $request->sortBy ?? 'qty';
    //     $orderBy = $request->orderBy ?? 'desc';

    //     // select from table with filter
    //     try {
    //         $data = Order::join('order_details', 'order_details.order_id', '=', 'orders.id')->join('product_categories', 'product_categories.id', '=', 'order_details.product_category_id')
    //             ->selectRaw('order_details.description,order_details.product_category_id,product_categories.name AS category_name,sum(order_details.qty) AS qty')
    //             ->when($product_name, function ($query) use ($product_name) {
    //                 $query->where('order_details.description', 'like', '%' . $product_name . '%');
    //             })
    //             ->when($product_category_id > 0, function ($query) use ($product_category_id) {
    //                 $query->where('order_details.product_category_id',  $product_category_id);
    //             })
    //             ->when($from_date, function ($query) use ($from_date) {
    //                 $query->where('orders.created_at', '>=', $from_date);
    //             })
    //             ->when($to_date, function ($query) use ($to_date) {
    //                 $query->where('orders.created_at', '<=', $to_date);
    //             })
    //             ->groupBy('order_details.description', 'order_details.product_category_id', 'product_categories.name')
    //             ->orderBy($sortBy, $orderBy)
    //             ->paginate(50);
    //         $response['success'] = true;
    //         $response['data'] = $data;
    //     } catch (Exception $ex) {
    //         abort($ex->getCode(), $ex->getMessage());
    //     }
    //     // return back to compoment
    //     return response()->json($response);
    // }


    public function productSummary(Request $request)
{
    try {
        /*
        |--------------------------------------------------------------------------
        | Normalize Inputs
        |--------------------------------------------------------------------------
        */
        $productName       = $request->product_name;
        $categoryId        = (int) ($request->product_category_id ?? 0);
        $fromDate          = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $toDate            = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;
        $perPage           = $request->per_page ?? 50;

        // ✅ Whitelist sorting (IMPORTANT)
        $allowedSorts = ['qty', 'description', 'category_name'];
        $sortBy  = in_array($request->sortBy, $allowedSorts) ? $request->sortBy : 'qty';
        $orderBy = strtolower($request->orderBy) === 'asc' ? 'asc' : 'desc';

        /*
        |--------------------------------------------------------------------------
        | Query
        |--------------------------------------------------------------------------
        */
        $query = Order::query()
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('product_categories', 'product_categories.id', '=', 'order_details.product_category_id')
            ->selectRaw('
                order_details.description,
                order_details.product_category_id,
                product_categories.name AS category_name,
                SUM(order_details.qty) AS qty
            ')

            // Filters
            ->when($productName, fn ($q) =>
                $q->where('order_details.description', 'like', "%{$productName}%")
            )

            ->when($categoryId > 0, fn ($q) =>
                $q->where('order_details.product_category_id', $categoryId)
            )

            ->when($fromDate && $toDate, fn ($q) =>
                $q->whereBetween('orders.created_at', [$fromDate, $toDate])
            )

            ->when($fromDate && !$toDate, fn ($q) =>
                $q->where('orders.created_at', '>=', $fromDate)
            )

            ->when(!$fromDate && $toDate, fn ($q) =>
                $q->where('orders.created_at', '<=', $toDate)
            )

            // Grouping
            ->groupBy(
                'order_details.description',
                'order_details.product_category_id',
                'product_categories.name'
            )

            // Sorting
            ->orderBy($sortBy, $orderBy);

        $data = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);

    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch product summary'
        ], 500);
    }
}

    // public function exportProductSummary(Request $request)
    // {
    //     // get param value
    //     $product_name = $request->product_name;
    //     $product_category_id = $request->product_category_id ?? 0;
    //     $from_date = null;
    //     $to_date = null;
    //     if ($request->from_date)
    //         $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
    //     if ($request->to_date)
    //         $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));
    //     $sortBy = $request->sortBy ?? 'qty';
    //     $orderBy = $request->orderBy ?? 'desc';

    //     // select from table with filter
    //     try {
    //         $data = Order::join('order_details', 'order_details.order_id', '=', 'orders.id')->join('product_categories', 'product_categories.id', '=', 'order_details.product_category_id')
    //             ->selectRaw('order_details.description,order_details.product_category_id,product_categories.name AS category_name,sum(order_details.qty) AS qty')
    //             ->when($product_name, function ($query) use ($product_name) {
    //                 $query->where('order_details.description', 'like', '%' . $product_name . '%');
    //             })
    //             ->when($product_category_id > 0, function ($query) use ($product_category_id) {
    //                 $query->where('order_details.product_category_id',  $product_category_id);
    //             })
    //             ->when($from_date, function ($query) use ($from_date) {
    //                 $query->where('orders.created_at', '>=', $from_date);
    //             })
    //             ->when($to_date, function ($query) use ($to_date) {
    //                 $query->where('orders.created_at', '<=', $to_date);
    //             })
    //             ->groupBy('order_details.description', 'order_details.product_category_id', 'product_categories.name')
    //             ->orderBy($sortBy, $orderBy)
    //             ->get();

    //         // Optionally add headers (first row)+
    //         $exportData = [];
    //         $exportData[] = ['ID', 'Product Name', 'Product Category', 'Quantity']; // Header row
    //         foreach ($data as $index => $value) {
    //             $exportData[] = [$index + 1, $value->description, $value->category_name, $value->qty];
    //         }
    //         return Excel::download(new ExportDataToExcel($exportData), 'Product Summary Report.xlsx');
    //     } catch (Exception $ex) {
    //         abort($ex->getCode(), $ex->getMessage());
    //     }
    // }



    private function productSummaryQuery($request)
{
    $productName = $request->product_name;
    $categoryId  = (int) ($request->product_category_id ?? 0);

    $fromDate = $request->from_date
        ? Carbon::parse($request->from_date)->startOfDay()
        : null;

    $toDate = $request->to_date
        ? Carbon::parse($request->to_date)->endOfDay()
        : null;

    $query = Order::query()
        ->join('order_details', 'order_details.order_id', '=', 'orders.id')
        ->join('product_categories', 'product_categories.id', '=', 'order_details.product_category_id')
        ->selectRaw('
            order_details.description,
            order_details.product_category_id,
            product_categories.name AS category_name,
            SUM(order_details.qty) AS qty
        ')

        ->when($productName, fn ($q) =>
            $q->where('order_details.description', 'like', "%{$productName}%")
        )

        ->when($categoryId > 0, fn ($q) =>
            $q->where('order_details.product_category_id', $categoryId)
        )

        ->when($fromDate && $toDate, fn ($q) =>
            $q->whereBetween('orders.created_at', [$fromDate, $toDate])
        )

        ->when($fromDate && !$toDate, fn ($q) =>
            $q->where('orders.created_at', '>=', $fromDate)
        )

        ->when(!$fromDate && $toDate, fn ($q) =>
            $q->where('orders.created_at', '<=', $toDate)
        )

        ->groupBy(
            'order_details.description',
            'order_details.product_category_id',
            'product_categories.name'
        );

    return $query;
}


// public function saleHistory(Request $request)
// {
//     $request->validate([
//         'transaction_id' => 'nullable|string',
//         'from_date' => 'nullable|date',
//         'to_date' => 'nullable|date',
//         'sortBy' => 'nullable|string',
//         'orderBy' => 'nullable|in:asc,desc'
//     ]);

//     $transactionId = $request->transaction_id;

//     $fromDate = $request->from_date
//         ? Carbon::parse($request->from_date)->startOfDay()
//         : null;

//     $toDate = $request->to_date
//         ? Carbon::parse($request->to_date)->endOfDay()
//         : null;

//     $allowedSorts = ['created_at', 'grand_total', 'total'];
//     $sortBy = in_array($request->sortBy, $allowedSorts) ? $request->sortBy : 'created_at';
//     $orderBy = $request->orderBy ?? 'desc';

//     $query = Order::with('order_details')
//         ->join('users', 'users.id', '=', 'orders.created_by_id')
//         ->select(
//             'orders.id',
//             'orders.total',
//             'orders.transaction_id',
//             'orders.total_discount',
//             'orders.grand_total',
//             'orders.mode_of_payment',
//             'orders.created_at',
//             DB::raw('users.username')
//         );

//     // Transaction search
//     if ($transactionId) {
//         $query->where('orders.transaction_id', 'like', "%{$transactionId}%");
//     }
//     // Date search
//     if ($fromDate && $toDate) {
//         $query->whereBetween('orders.created_at', [$fromDate, $toDate]);
//     } elseif ($fromDate) {
//         $query->whereDate('orders.created_at', '>=', $fromDate);
//     } elseif ($toDate) {
//         $query->whereDate('orders.created_at', '<=', $toDate);
//     }

//     $orders = $query
//         ->orderBy($sortBy, $orderBy)
//         ->paginate(50);

//     return response()->json([
//         'success' => true,
//         'data' => $orders
//     ]);
// }
   



public function saleHistory(Request $request)
{
    $request->validate([
        'transaction_id' => 'nullable|string',
        'from_date'      => 'nullable|date',
        'to_date'        => 'nullable|date',
        'sortBy'         => 'nullable|string',
        'orderBy'        => 'nullable|in:asc,desc',
        'per_page'       => 'nullable|integer|min:1|max:100'
    ]);

    try {
        /*
        |--------------------------------------------------------------------------
        | Normalize Inputs
        |--------------------------------------------------------------------------
        */
        $transactionId = $request->transaction_id;

        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : null;

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : null;

        $perPage = $request->per_page ?? 50;

        // ✅ Safe sorting
        $allowedSorts = ['created_at', 'grand_total', 'total'];
        $sortBy  = in_array($request->sortBy, $allowedSorts) ? $request->sortBy : 'created_at';
        $orderBy = $request->orderBy === 'asc' ? 'asc' : 'desc';

        /*
        |--------------------------------------------------------------------------
        | Query
        |--------------------------------------------------------------------------
        */
        $query = Order::query()
            ->with('order_details')
            ->join('users', 'users.id', '=', 'orders.created_by_id')
            ->select([
                'orders.id',
                'orders.total',
                'orders.transaction_id',
                'orders.total_discount',
                'orders.grand_total',
                'orders.mode_of_payment',
                'orders.created_at',
                'users.username'
            ])

            // Filters
            ->when($transactionId, fn ($q) =>
                $q->where('orders.transaction_id', 'like', "%{$transactionId}%")
            )

            ->when($fromDate && $toDate, fn ($q) =>
                $q->whereBetween('orders.created_at', [$fromDate, $toDate])
            )

            ->when($fromDate && !$toDate, fn ($q) =>
                $q->where('orders.created_at', '>=', $fromDate)
            )

            ->when(!$fromDate && $toDate, fn ($q) =>
                $q->where('orders.created_at', '<=', $toDate)
            )

            ->orderBy("orders.$sortBy", $orderBy);

        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $orders
        ]);

    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch sales history'
        ], 500);
    }
}
    public function saleHistorySummary(Request $request)
    {
        // get param value
        $invoice_no = $request->invoice_no;
        $from_date = null;
        $to_date = null;
        if ($request->from_date)
            $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
        if ($request->to_date)
            $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));
        try {
            $data = Order::select(DB::raw("sum(grand_total) as grand_total, sum(total_discount) as total_discount, sum(net_amount) as net_amount"))
                ->when($invoice_no, function ($query) use ($invoice_no) {
                    $query->where('invoice_no', 'like', '%' . $invoice_no . '%');
                })
                ->when($from_date, function ($query) use ($from_date) {
                    $query->where('created_at', '>=', $from_date);
                })
                ->when($to_date, function ($query) use ($to_date) {
                    $query->where('created_at', '<=', $to_date);
                })
                ->first();

            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        // return back to compoment
        return response()->json($response);
    }

    public function exportSaleHistory(Request $request)
    {
        $request->validate([
            'transaction_id' => 'nullable|string',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'sortBy' => 'nullable|string',
            'orderBy' => 'nullable|in:asc,desc'
        ]);
    
        $transactionId = $request->transaction_id;
    
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : null;
    
        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : null;
    
        $allowedSorts = ['created_at', 'grand_total', 'total'];
        $sortBy = in_array($request->sortBy, $allowedSorts) ? $request->sortBy : 'created_at';
        $orderBy = $request->orderBy ?? 'desc';
    
        $query = Order::with('order_details')
            ->join('users', 'users.id', '=', 'orders.created_by_id')
            ->select(
                'orders.id',
                'orders.total',
                'orders.transaction_id',
                'orders.total_discount',
                'orders.grand_total',
                'orders.mode_of_payment',
                'orders.created_at',
                DB::raw('users.username')
            );
    
        // Transaction search
        if ($transactionId) {
            $query->where('orders.transaction_id', 'like', "%{$transactionId}%");
        }
        // Date search
        if ($fromDate && $toDate) {
            $query->whereBetween('orders.created_at', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->whereDate('orders.created_at', '>=', $fromDate);
        } elseif ($toDate) {
            $query->whereDate('orders.created_at', '<=', $toDate);
        }
    
        $orders = $query
            ->orderBy($sortBy, $orderBy)
            ->paginate(50);
    
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
    public function showOrderDetail($id)
    {
        try {
            $data = Order::with(['order_details', 'operator'])->join('tables', 'tables.id', '=', 'orders.table_id')
                ->join('users', 'users.id', '=', 'orders.created_by_id')
                ->select(
                    'orders.total',
                    'orders.net_amount',
                    'orders.discount',
                    'orders.invoice_no',
                    'tables.name AS table_name',
                    'orders.created_at',
                    'orders.id',
                    'orders.created_by_id',
                    'orders.receive_amount',
                    DB::raw('users.username AS cashier')
                )
                ->find($id);

            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        // return back to compoment
        return response()->json($response);
    }
}
