<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAlerts;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockAlertsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function getStockAlerts()
    {
        $stockalert = StockAlerts::select('id', 'very_low_stock', 'low_stock')
            ->latest()
            ->first();
    
        // ✅ Handle missing config
        if (!$stockalert) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
    
        $products = Product::select('id', 'name', 'inventory', 'expiry_date')
            ->where('inventory', '<=', $stockalert->low_stock)
            ->get()
            ->map(function ($product) use ($stockalert) {
    
                if ($product->inventory <= $stockalert->very_low_stock) {
                    $product->stock_status = 'very_low';
                } else {
                    $product->stock_status = 'low';
                }
    
                return $product;
            });
    
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }


  

    public function getExpiryAlerts()
    {
        $stockalert = StockAlerts::select('id', 'stock_alert_days')
            ->latest()
            ->first();
    
        $days = $stockalert?->stock_alert_days ?? 60;
    
        $today = Carbon::today();
        $limitDate = $today->copy()->addDays($days);
    
        $products = Product::select('id', 'name', 'expiry_date', 'inventory')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $limitDate) // include expired + upcoming
            ->get()
            ->map(function ($product) use ($today) {
    
                // ✅ Parse expiry_date correctly
                $expiryDate = Carbon::parse($product->expiry_date);
    
                // ✅ Calculate days left (negative = expired)
                $daysLeft = $today->diffInDays($expiryDate, false);
                $product->days_left = $daysLeft;
    
                // ✅ Set status
                if ($daysLeft < 0) {
                    $product->expiry_status = 'expired';
                } elseif ($daysLeft == 0) {
                    $product->expiry_status = 'expires_today';
                } elseif ($daysLeft <= 7) {
                    $product->expiry_status = 'critical';
                } elseif ($daysLeft <= 30) {
                    $product->expiry_status = 'warning';
                } else {
                    $product->expiry_status = 'normal';
                }
    
                return $product;
            })
            ->sortBy('days_left') // expired first
            ->values();
    
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }


    public function stockAlert()
    {
        try {
            $stockAlert = StockAlerts::query()
                ->latest()
                ->first();
    
            if (!$stockAlert) {
                return response()->json([
                    'success' => false,
                    'message' => 'No stock alert found',
                    'data' => null,
                ], 404);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Stock alert retrieved successfully',
                'data' => $stockAlert,
            ], 200);
    
        } catch (\Throwable $e) {
            report($e); // Log error
    
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch stock alert',
                'data' => null,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

// public function getAllAlerts()
// {
//     $stockalert = StockAlerts::latest()->first();

//     // ✅ Defaults
//     $lowStock = $stockalert->low_stock ?? 10;
//     $veryLowStock = $stockalert->very_low_stock ?? 5;
//     $expiryDays = $stockalert->stock_alert_days ?? 60;

//     $today = Carbon::today();
//     $limitDate = $today->copy()->addDays($expiryDays);

//     $products = Product::select('id', 'name', 'inventory', 'expiry_date')
//         ->where(function ($query) {
//             $query->where('inventory', '<=', 'stock_alert_qty_low')
//                   ->orWhere(function ($q) use ($limitDate) {
//                       $q->whereNotNull('expiry_date')
//                         ->whereDate('expiry_date', '<=', $limitDate);
//                   });
//         })
//         ->get()
//         ->map(function ($product) use ($today, $lowStock, $veryLowStock) {

//             // =========================
//             // ✅ STOCK STATUS
//             // =========================
//             if ($product->inventory <= $veryLowStock) {
//                 $product->stock_status = 'very_low';
//             } elseif ($product->inventory <= $lowStock) {
//                 $product->stock_status = 'low';
//             } else {
//                 $product->stock_status = 'enough';
//             }

//             // =========================
//             // ✅ EXPIRY STATUS
//             // =========================
//             if ($product->expiry_date) {
//                 $expiryDate = Carbon::parse($product->expiry_date);
//                 $daysLeft = $today->diffInDays($expiryDate, false);

//                 $product->days_left = $daysLeft;

//                 if ($daysLeft < 0) {
//                     $product->expiry_status = 'expired';
//                 } elseif ($daysLeft == 0) {
//                     $product->expiry_status = 'expires_today';
//                 } elseif ($daysLeft <= 7) {
//                     $product->expiry_status = 'critical';
//                 } elseif ($daysLeft <= 30) {
//                     $product->expiry_status = 'warning';
//                 } else {
//                     $product->expiry_status = 'normal';
//                 }
//             } else {
//                 $product->expiry_status = null;
//                 $product->days_left = null;
//             }

//             return $product;
//         })
//         ->sortBy(function ($product) {
//             return [
//                 $product->days_left ?? 9999,   // expiry priority
//                 $product->inventory           // stock priority
//             ];
//         })
//         ->values();

//     return response()->json([
//         'success' => true,
//         'data' => $products
//     ]);
// }





public function getAllAlerts()
{
    $today = Carbon::today();

    $products = Product::select(
            'id',
            'name',
            'inventory',
            'expiry_date',
            'stock_alert_days',
            'stock_alert_qty_low',
            'stock_alert_qty_very_low'
        )
        ->where(function ($query) use ($today) {

            // Products with low stock
            $query->whereColumn('inventory', '<=', 'stock_alert_qty_low')

                // OR products nearing expiry
                ->orWhere(function ($q) use ($today) {
                    $q->whereNotNull('expiry_date')
                      ->whereRaw(
                          "expiry_date <= DATE_ADD(?, INTERVAL stock_alert_days DAY)",
                          [$today->toDateString()]
                      );
                });
        })
        ->get()
        ->map(function ($product) use ($today) {

            // ======================
            // STOCK STATUS
            // ======================
            if ($product->inventory <= $product->stock_alert_qty_very_low) {
                $product->stock_status = 'very_low';
            } elseif ($product->inventory <= $product->stock_alert_qty_low) {
                $product->stock_status = 'low';
            } else {
                $product->stock_status = 'enough';
            }

            // ======================
            // EXPIRY STATUS
            // ======================
            if (!empty($product->expiry_date)) {

                $expiryDate = Carbon::parse($product->expiry_date);
                $daysLeft = $today->diffInDays($expiryDate, false);

                $product->days_left = $daysLeft;

                if ($daysLeft < 0) {
                    $product->expiry_status = 'expired';
                } elseif ($daysLeft == 0) {
                    $product->expiry_status = 'expires_today';
                } elseif ($daysLeft <= 7) {
                    $product->expiry_status = 'critical';
                } elseif ($daysLeft <= 30) {
                    $product->expiry_status = 'warning';
                } else {
                    $product->expiry_status = 'normal';
                }

            } else {
                $product->days_left = null;
                $product->expiry_status = null;
            }

            return $product;
        })
        ->sortBy(function ($product) {

            return [
                $product->days_left ?? 999999,
                $product->inventory
            ];

        })
        ->values();

    return response()->json([
        'success' => true,
        'data' => $products
    ]);
}




public function list(Request $request)
{
    // get param value
    $very_low_stock = $request->very_low_stock;
    $low_stock = $request->low_stock;
    $sortBy = $request->sortBy ?? 'created_at';
    $orderBy = $request->orderBy ?? 'desc';
    try {
        $data = StockAlerts::select('id', 'very_low_stock', 'low_stock', 'stock_alert_days', 'created_at')
            ->when($very_low_stock, function ($query) use ($very_low_stock) {
                $query->where('very_low_stock', 'like', '%' . $very_low_stock . '%');
            })
            ->when($low_stock, function ($query) use ($low_stock) {
                $query->where('low_stock', 'like', '%' . $low_stock . '%');
            })
            ->limit(100)
            ->orderBy($sortBy, $orderBy)
            ->paginate(50);
        $response['success'] = true;
        $response['data'] = $data;
    } catch (Exception $ex) {
        abort($ex->getCode(), $ex->getMessage());
    }

    return response()->json($response);
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
public function save(Request $request)
{
    sleep(1);
    $validator = Validator::make($request->all(), [
        'very_low_stock' => 'required|unique:stock_alerts,very_low_stock,' . $request->id,
        'low_stock' => 'required|unique:stock_alerts,low_stock,' . $request->id,
        'stock_alert_days'  => 'required|unique:stock_alerts,stock_alert_days,' . $request->id,
    ]);
    if ($validator->fails()) {
        return response()->json(
            [
                'success' => false,
                'errors' => $validator->errors()
            ]
        );
    }
    try {
        // DB::beginTransaction();
        if ($request->id > 0) {
            $data = StockAlerts::find($request->id);
        } else {
            $data = new StockAlerts();
            $data->created_by_id = $request->user()->id;
        }

        $data->updated_by_id = $request->user()->id;
        $data->very_low_stock = $request->very_low_stock;
        $data->low_stock = $request->low_stock;
        $data->stock_alert_days = $request->stock_alert_days;


       // $data->order = $request->order;
        $data->save();
        $response['success'] = true;
        $response['id'] =$data->id;
        // DB::commit();
    } catch (Exception $ex) {
        abort($ex->getCode(), $ex->getMessage());
    }
    return response()->json($response);
}


public function deleteStockAlert(Request $request)
{
    $data = StockAlerts::findOrFail($request->id);
    $data->deleted_id = $request->user()->id;
    $data->delete();
    return response()->json();
}

    
}
