<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TableController extends Controller
{
    public function list(Request $request)
    {
        // get param value
        $name = $request->name;
        $status = $request->status ?? 0;
        $sortBy = $request->sortBy ?? 'created_at';
        $orderBy = $request->orderBy ?? 'desc';
        try {
            $data = Table::select('id', 'name', 'status', 'order', 'created_at')
                ->when($name, function ($query) use ($name) {
                    $query->where('name', 'like', '%' . $name . '%');
                })
                ->when($status > 0, function ($query) use ($status) {
                    $query->where('status',  $status);
                })
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:tables,name,' . $request->id,
            'status' => 'required',
            'order' => 'nullable|numeric',
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
                $data = Table::find($request->id);
            } else {
                $data = new Table();
                $data->created_by_id = $request->user()->id;
            }

            $data->updated_by_id = $request->user()->id;
            $data->name = $request->name;
            $data->status = $request->status;
            $data->order = $request->order;
            $data->save();
            $response['success'] = true;
            $response['data'] = null;
            // DB::commit();
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        return response()->json($response);
    }

    public function edit(Request $request)
    {
        return response()->json(Table::select('id', 'name', 'status', 'order')->findOrFail($request->id));
    }

    public function delete(Request $request)
    {
        $data = Table::findOrFail($request->id);
        $data->deleted_id = $request->user()->id;
        $data->delete();
        return response()->json();
    }
}
