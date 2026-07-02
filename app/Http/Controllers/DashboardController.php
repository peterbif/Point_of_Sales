<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
   
    
    public function __invoke(): JsonResponse
    {
        try {
            $today = Carbon::today();
            $startDate = $today->copy()->subDays(29); // last 30 days including today
    
            /*
            |--------------------------------------------------------------------------
            | Top Products for 30 days
            |--------------------------------------------------------------------------
            */
            $topProducts = Order::query()
                ->join('order_details', 'order_details.order_id', '=', 'orders.id')
                // ->whereDate('orders.created_at', $today)
                ->whereBetween('orders.created_at', [$startDate, $today->copy()->endOfDay()])

                ->select([
                    'order_details.description',
                    DB::raw('SUM(order_details.qty) as qty')
                ])
                ->groupBy('order_details.description')
                ->orderByDesc('qty') // FIXED (was inventory)
                ->limit(10)
                ->get();
    
            /*
            |--------------------------------------------------------------------------
            | Sales by Categories for 30 days
            |--------------------------------------------------------------------------
            */
            $saleCategories = Order::query()
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->join('product_categories', 'product_categories.id', '=', 'order_details.product_category_id')
                // ->whereDate('orders.created_at', $today)
                ->whereBetween('orders.created_at', [$startDate, $today->copy()->endOfDay()])

                ->select([
                    'product_categories.name',
                    DB::raw('
                        SUM(
                            (order_details.qty * order_details.unit_price * order_details.discount / 100) +
                            (order_details.qty * order_details.unit_price * (1 - order_details.discount / 100) * orders.discount / 100)
                        ) as discount
                    '),
                    DB::raw('SUM(order_details.qty * order_details.unit_price) as total')
                ])
                ->groupBy('product_categories.name')
                ->orderBy('product_categories.name')
                ->get();
    
            /*
            |--------------------------------------------------------------------------
            | Last 30 Days Sales
            |--------------------------------------------------------------------------
            */
            $salesData = Order::query()
                ->whereDate('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, SUM(grand_total) as total')
                ->groupBy('date')
                ->pluck('total', 'date'); // KEY IMPROVEMENT
    
            $result = collect(range(0, 29))->map(function ($i) use ($startDate, $salesData) {
                $date = $startDate->copy()->addDays($i)->toDateString();
    
                return [
                    'date' => Carbon::parse($date)->format('d-M'),
                    'total' => (float) ($salesData[$date] ?? 0)
                ];
            });
    
        } catch (\Throwable $e) {
            report($e);
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data'
            ], 500);
        }
    
        return response()->json([
            'success' => true,
            'bar_data' => $result,
            'sale_categories' => $saleCategories,
            'top_products' => $topProducts
        ]);
    }
}
