<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ProductCategoryController extends Controller
{
    public function list(Request $request)
    {
        // get param value
        $name = $request->name;
        $sortBy = $request->sortBy ?? 'created_at';
        $orderBy = $request->orderBy ?? 'desc';
        try {
            $data = ProductCategory::select('id', 'name',  'created_at')
                ->when($name, function ($query) use ($name) {
                    $query->where('name', 'like', '%' . $name . '%');
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


    public function searchCategory(Request $request)
{

    //  return 1;.
    $query = ProductCategory::query();

    if ($request->filled('name')) {
        $query->where('name', 'like', "%{$request->name}%");
    }

    return response()->json([
        'success' => true,
        'data' => $query->latest()->get(),
    ]);
}


    // public function save(Request $request)
    // {
    //     sleep(1);
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|unique:product_categories,name,' . $request->id,
    //         //'order' => 'nullable|numeric',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(
    //             [
    //                 'success' => false,
    //                 'errors' => $validator->errors()
    //             ]
    //         );
    //     }
    //     try {
    //         // DB::beginTransaction();
    //         if ($request->id > 0) {
    //             $data = ProductCategory::find($request->id);
    //         } else {
    //             $data = new ProductCategory();
    //             $data->created_by_id = $request->user()->id;
    //         }

    //         $data->updated_by_id = $request->user()->id;
    //         $data->name = $request->name;
    //        // $data->order = $request->order;
    //         $data->save();
    //         $response['success'] = true;
    //         $response['id'] =$data->id;
    //         // DB::commit();
    //     } catch (Exception $ex) {
    //         abort($ex->getCode(), $ex->getMessage());
    //     }
    //     return response()->json($response);
    // }





    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('product_categories', 'name')->ignore($request->id),
            ],
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }
    
        try {
            $data = $request->filled('id')
                ? ProductCategory::findOrFail($request->id)
                : new ProductCategory();
    
            if (!$request->filled('id')) {
                $data->created_by_id = $request->user()->id;
            }
    
            $data->updated_by_id = $request->user()->id;
            $data->name = $request->name;
            $data->save();
    
            return response()->json([
                'success' => true,
                'id' => $data->id,
                'message' => $request->filled('id')
                    ? 'Category updated successfully.'
                    : 'Category created successfully.',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request)
    {
        return response()->json(ProductCategory::select('id', 'name')->findOrFail($request->id));
    }

    public function delete(Request $request)
    {
        $data = ProductCategory::findOrFail($request->id);
        $data->deleted_id = $request->user()->id;
        $data->delete();
        return response()->json();
    }
}
