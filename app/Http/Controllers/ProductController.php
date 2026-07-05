<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductInventory;
use App\Models\StockAlerts;
use Carbon\Carbon;
use Exception;
use Faker\Core\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // public function list(Request $request)
    // {

    //   //  return $request->all();

    //   $stockalert = StockAlerts::latest()->first();


    //     // get param value
    //     $name = $request->name;

    //     $product_category_id = $request->product_category_id ?? 0;
    //      $sortBy = $request->sortBy ?? "products.updated_at";
    //     // $sortBy = $request->sortBy ?? 'updated_at';\
    //     $orderBy = $request->orderBy ?? 'desc';
    //     try {
    //         // product list
    //         $data = Product::join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
    //             ->when($name, function ($query) use ($name) {
    //                 $query->where('products.name', 'like', '%' . $name . '%');
    //             })
    //             ->when($product_category_id > 0, function ($query) use ($product_category_id) {
    //                 $query->where('products.product_category_id', '=', $product_category_id);
    //             })
    //             ->select('products.id', 'products.name', 'products.image', 'products.inventory', 'products.unit_price', 'products.barcode', 'products.expiry_date','products.created_at', 'product_categories.name as category_name')
    //             ->orderBy($sortBy, $orderBy)
    //             ->limit(50)
    //             ->paginate(20);

    //         $response['success'] = true;
    //         $response['data'] = $data;
    //     } catch (Exception $ex) {
    //         abort($ex->getCode(), $ex->getMessage());
    //     }


        

    //     return response()->json(['data' => $response,
    //     'stockalert' => $stockalert
                             
    
    // ]);
    // }



    public function searchProduct(Request $request)
{
    try {
        // Latest stock alert
      //  $stockAlert = StockAlerts::latest()->first();

        // Validate inputs
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'product_category_id' => 'nullable|integer',
            'sortBy' => 'nullable|string|in:products.updated_at,products.name,products.unit_price,products.created_at',
            'orderBy' => 'nullable|string|in:asc,desc',
        ]);

        // Defaults
        $name = $validated['name'] ?? null;
        $productCategoryId = $validated['product_category_id'] ?? null;
        $sortBy = $validated['sortBy'] ?? 'products.updated_at';
        $orderBy = $validated['orderBy'] ?? 'desc';

        // Query
        $products = Product::query()
            ->join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->when($name, fn ($query) =>
                $query->where('products.name', 'like', "%{$name}%")
            )
            ->when($productCategoryId, fn ($query) =>
                $query->where('products.product_category_id', $productCategoryId)
            )
            ->select([
                'products.id',
                'products.name',
                'products.image',
                'products.inventory',
                'products.unit_price',
                'products.barcode',
                'products.expiry_date',
                'products.created_at',
                'products.wholesales_price',
                'products.stock_alert_days',
                'products.stock_alert_qty_very_low',
                'products.stock_alert_qty_low',

                'product_categories.name as category_name',
            ])
            ->orderBy($sortBy, $orderBy)
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products,
            //'stockalert' => $stockAlert,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}

    public function list(Request $request)


{
    try {
        // Latest stock alert
      //  $stockAlert = StockAlerts::latest()->first();

        // Validate inputs
        $validated = $request->validate([
        'sortBy' => 'nullable|string|in:products.updated_at,products.name,products.unit_price,products.created_at',
            'orderBy' => 'nullable|string|in:asc,desc',
        ]);

 
        $sortBy = $validated['sortBy'] ?? 'products.updated_at';
        $orderBy = $validated['orderBy'] ?? 'desc';

        // Query
        $products = Product::query()
            ->join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->select([
                'products.id',
                'products.name',
                'products.image',
                'products.inventory',
                'products.unit_price',
                'products.barcode',
                'products.expiry_date',
                'products.created_at',
                'products.wholesales_price',
                'products.stock_alert_days',
                'products.stock_alert_qty_very_low',
                'products.stock_alert_qty_low',

                'product_categories.name as category_name',
            ])
            ->orderBy($sortBy, $orderBy)
            // ->limit(20)
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products,
            //'stockalert' => $stockAlert,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
    
    public function getAllProducts(){

        try {
            // product list
            $data = Product::with('category')
            
            ->orderBy('name', 'ASC')->get();

            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }

        return response()->json($response);




    }


    public function getAllProductCategories(){

        try {
            // product list
            $data = ProductCategory::orderBy('name', 'ASC')->get();

            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }

        return response()->json($response);

    }

    public function productinventory(Request $request)
    {

       // return $request->all();
        try {
    
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'product_category_id' => 'nullable|integer',
                'sortBy' => 'nullable|string',
                'orderBy' => 'nullable|in:asc,desc',
            ]);
    
            $allowedSorts = [
                'name' => 'products.name',
                'created_at' => 'products.created_at',
                'updated_at' => 'products.updated_at',
                'total_stock' => 'total_stock',
            ];
    
            $sortBy = $allowedSorts[$validated['sortBy'] ?? 'updated_at']
                      ?? 'products.updated_at';
    
            $orderBy = $validated['orderBy'] ?? 'desc';
    
            $query = Product::query()
    
                // Sum inventory column
                ->with(['inventories', 'category'])
    
                // Filter by name (LIKE recommended for POS search)
                ->when($validated['name'] ?? null, function ($q, $name) {
                    $q->where('products.name', 'like', "%{$name}%");
                })
    
                // Filter by category
                ->when($validated['product_category_id'] ?? null, function ($q, $categoryId) {
                    $q->where('products.product_category_id', $categoryId);
                })
    
                ->orderBy($sortBy, $orderBy);
    
            $data = $query->paginate(50);
    
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
    
        } catch (\Throwable $e) {
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product inventory.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function save(Request $request)
    // {
    //     $validator = Validator::make(
    //         $request->all(),
    //         [
    //             'name' => 'required|unique:tables,name,' . $request->id,
    //             'image' => 'nullable|image|mimes:png,jpg,jpeg, webp|max:1024',
    //             'product_category_id' => 'required',
    //             'unit_price' => 'required|numeric',
    //             'inventory' => 'required|numeric',
                
    //         ],
    //         [],
    //         [
    //             'product_category_id' => 'product category',
    //         ]
    //     );
    //     if ($validator->fails()) {
    //         return response()->json(
    //             [
    //                 'success' => false,
    //                 'errors' => $validator->errors()
    //             ]
    //         );
    //     }
            
    //     DB::transaction(function ($request) {
            

    //         try {
    //             // DB::beginTransaction();
    //             if ($request->id > 0) {
    //                 $data = Product::find($request->id);
    //             } else {
    //                 $data = new Product();
    //                 $data->created_by_id = $request->user()->id;
    //             }
    
    //             $data->updated_by_id = $request->user()->id;
    //             $data->name = $request->name;
    //             $data->product_category_id = $request->product_category_id;
    //             $data->unit_price = $request->unit_price;
    //             $data->name = $request->name;
    //             $data->inventory = $request->inventory;
    
    //             // delete uploaded file
    //             if ($request->is_deleted_image == 1 && $request->id > 0) {
    //                 if (Storage::disk('public')->exists($data->image)) {
    //                     Storage::disk('public')->delete($data->image);
    //                 }
    //                 $data->image = '';
    //             }
    //             // upload file
    //             else if ($request->hasFile('image')) {
    //                 if ($data->image && Storage::disk('public')->exists($data->image)) {
    //                     Storage::disk('public')->delete($data->image);
    //                 }
    //                 $data->image = Storage::disk('public')->put('product', $request->image);
    //             }
    
    //             if($data->save()){
    
    //                 $product_category = new ProductCategory();
    //                 $product_category->product_id = $data->id;
    //                 $product_category->inventory = $data->inventory;
    //                 $product_category->save();
    
    //             }


    //             $response['success'] = true;
    //             $response['data'] = null;
    //             // DB::commit();
    //         } catch (Exception $ex) {
    //             abort($ex->getCode(), $ex->getMessage());
    //         }
    //         return response()->json($response);
    //     });

    


        
    // }


    public function addQuantity(Request $request)
    {
        $validated = $request->validate([
            'id'        => ['required', 'exists:products,id'],
            'inventory' => ['required', 'numeric'],
        ], 
        [],
                [
            'id' => 'product',
        ]);
    
        try {
            $product = DB::transaction(function () use ($request, $validated) {
    
                // Lock row to prevent race conditions
                $product = Product::lockForUpdate()->findOrFail($validated['id']);
    
                // Increment safely at database level
                $product->increment('inventory', $validated['inventory']);
    
                // Store inventory log/history
                $product->inventories()->create([
                    'inventory' => $validated['inventory'],
                    'user_id' => $request->user()->id,
                ]);
    
               // return $product->fresh(); // return updated model
            });
    
            return response()->json([
                'success' => true,
                'data'    => $product
            ]);
    
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory.'
            ], 500);
        }
    }



    public function productDeleted(Request $request)
    {
        $deletedProduct = Product::onlyTrashed()
            ->when($request->name, function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->name . '%');
            })
            ->orderBy('name')
            ->get();
    
        return response()->json([
            'success' => true,
            'data' => $deletedProduct
        ]);
    }


    public function editDeleted(Request $request, $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
    
        $product->restore();
    
        $product->updated_by_id = $request->user()->id;
        $product->save();
    
        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }
   
   
    public function save(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'nullable|exists:products,id',
                'name' => 'required|unique:products,name,' . $request->id,
                'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024',
                'product_category_id' => 'required|exists:product_categories,id',
                'unit_price' => 'required|numeric|min:0',
                'inventory' => 'required|numeric|min:1',
                'wholesales_price'=> 'nullable',
                'stock_alert_days' => 'required|numeric',
                'stock_alert_qty_very_low' => 'required|numeric',
                'stock_alert_qty_low' => 'required|numeric',
                'barcode' => [ 'nullable', 'string',  'max:100', 'unique:products,barcode,' . $request->id,],
                'expiry_date' => ['required', 'date'],


            ],
            [],
            [
                'product_category_id' => 'product category',
                'name' => 'Product'
            ]
        );
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            $data = DB::transaction(function () use ($request) {
    
                // Create or Update
                $product = Product::updateOrCreate(
                    ['id' => $request->id],
                    [
                        'name' => $request->name,
                        'product_category_id' => $request->product_category_id,
                        'unit_price' => $request->unit_price,
                        'inventory' => $request->inventory,
                        'updated_by_id' => $request->user()->id,
                       'expiry_date' => Carbon::parse($request->expiry_date)->format('Y-m-d'),
                       'barcode' => $request->filled('barcode') ? $request->barcode : null,
                       'wholesales_price'=>  $request->wholesales_price,
                       'stock_alert_days' =>  $request->stock_alert_days,
                        'stock_alert_qty_very_low' =>  $request->stock_alert_qty_very_low,
                        'stock_alert_qty_low' =>  $request->stock_alert_qty_low,
                                
                    ]
                );

                $product->inventories()->create([
                    'inventory' => $request->inventory,
                    'user_id'=>  $request->user()->id,
                   
                   
                ]);
    
                if (!$request->id) {
                    $product->created_by_id = $request->user()->id;
                    $product->save();
                }
    
                // Handle image deletion
                if ($request->is_deleted_image == 1 && $product->image) {
                    Storage::disk('public')->delete($product->image);
                    $product->image = null;
                    $product->save();
                }
    
                // Handle image upload
                if ($request->hasFile('image')) {
                    if ($product->image) {
                        Storage::disk('public')->delete($product->image);
                    }
    
                    $path = $request->file('image')->store('product', 'public');
                    $product->image = $path;
                    $product->save();
                }
    
               return $product;

            });
    
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
    
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }
   
   
   
   
    // public function edit(Request $request)


    // {
    //     return response()->json(Product::join('product_categories', 'product_categories.id', '=', 'products.product_category_id')->select('products.id as id', 'products.name as name', 'product_category_id', 'product_categories.name as category_name', 'unit_price', 'image', 'products.inventory', 'products.barcode', 'products.expiry_date',  'products.wholesales_price', 'products.stock_alert_days', 'products.stock_alert_qty_very_low','products.stock_alert_qty_low')->findOrFail($request->id));
    // }



    public function edit(Request $request)
{
    return response()->json(
        Product::join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->select(
                'products.id',
                'products.name',
                'products.product_category_id',
                'product_categories.name as category_name',
                'products.unit_price',
                'products.image',
                'products.inventory',
                'products.barcode',
                'products.expiry_date',
                'products.wholesales_price',
                'products.stock_alert_days',
                'products.stock_alert_qty_very_low',
                'products.stock_alert_qty_low'
            )
            ->where('products.id', $request->id)
            ->firstOrFail()
    );
}

public function delete(Request $request)
{
    $data = Product::findOrFail($request->id);

    $data->deleted_by_id = $request->user()->id;
    $data->save();

    if ($data->image && Storage::disk('public')->exists($data->image)) {
        Storage::disk('public')->delete($data->image);
    }

    $data->delete();

    return response()->json([
        'success' => true
    ]);
}

    public function categoryList()
    {
        return response()->json(ProductCategory::select('id', 'name')->orderBy('name')->get());
    }
}
