<?php

namespace App\Http\Controllers;

use App\Models\BalanceAdjustment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BalanceAdjustmentController extends Controller
{
    public function list(Request $request)
    {
        // get param value
        $remark = $request->remark;
        $type_id = $request->type_id ?? 0;
        $sortBy = $request->sortBy ?? 'created_at';
        $orderBy = $request->orderBy ?? 'desc';
        $from_date = null;
        $to_date = null;
        if ($request->from_date)
            $from_date = date('Y-m-d 00:00:00', strtotime($request->from_date));
        if ($request->to_date)
            $to_date = date('Y-m-d 23:59:59', strtotime($request->to_date));
        try {
            $data = BalanceAdjustment::select('id', 'amount', 'remark', 'type_id', 'adjustment_date', 'created_at')
                ->when($remark, function ($query) use ($remark) {
                    $query->where('remark', 'like', '%' . $remark . '%');
                })
                ->when($type_id > 0, function ($query) use ($type_id) {
                    $query->where('type_id',  $type_id);
                })
                ->when($from_date, function ($query) use ($from_date) {
                    $query->where('created_at', '>=', $from_date);
                })
                ->when($to_date, function ($query) use ($to_date) {
                    $query->where('created_at', '<=', $to_date);
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
            'amount' => 'required|numeric',
            'type_id' => 'required',
            'adjustment_date' => 'required',
            'remark' => 'required'
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
                $data = BalanceAdjustment::find($request->id);
            } else {
                $data = new BalanceAdjustment();
                $data->created_by_id = $request->user()->id;
            }

            $data->updated_by_id = $request->user()->id;
            $data->amount = $request->amount;
            $data->type_id = $request->type_id;
            $data->adjustment_date = date('Y-m-d', strtotime($request->adjustment_date));
            $data->remark = $request->remark;
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
        return response()->json(BalanceAdjustment::select('id', 'amount', 'type_id', 'remark', 'adjustment_date')->findOrFail($request->id));
    }

    public function delete(Request $request)
    {
        $data = BalanceAdjustment::findOrFail($request->id);
        $data->deleted_id = $request->user()->id;
        $data->delete();
        return response()->json();
    }
}
