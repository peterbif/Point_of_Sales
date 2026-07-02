<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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


    public function save(Request $request)
    {
        sleep(3);
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|unique:product_categories,name,' . $request->id,
            //'order' => 'nullable|numeric',
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
                $data = ProductCategory::find($request->id);
            } else {
                $data = new ProductCategory();
                $data->created_by_id = $request->user()->id;
            }

            $data->updated_by_id = $request->user()->id;
            $data->name = $request->category_name;
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
