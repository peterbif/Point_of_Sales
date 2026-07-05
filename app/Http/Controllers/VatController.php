<?php

namespace App\Http\Controllers;

use App\Models\Vat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class VatController extends Controller

{

    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function list(Request $request)
    {
        // get param value
        $vat = $request->vat;
        $sortBy = $request->sortBy ?? 'created_at';
        $orderBy = $request->orderBy ?? 'desc';
        try {
            $data = Vat::select('id', 'vat',  'created_at')
                ->when($vat, function ($query) use ($vat) {
                    $query->where('name', 'like', '%' . $vat . '%');
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


    public function getVat()
    {
        $vat = Vat::select('id', 'vat')
            ->latest()
            ->first();

         $vat2 = $vat?->vat ?? 0;

    
        // ✅ Handle missing config
        if (!$vat) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
    
        return response()->json([
            'success' => true,
            'data' => $vat2
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
    public function save(Request $request)
    {
        sleep(1);
        $validator = Validator::make($request->all(), [
            'vat' => 'required|unique:vats,vat,' . $request->id,
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
                $data = Vat::find($request->id);
            } else {
                $data = new Vat();
                $data->created_by_id = $request->user()->id;
            }

            $data->updated_by_id = $request->user()->id;
            $data->vat = $request->vat;
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


    /**
     * Display the specified resource.
     */
    public function show(Vat $vat)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vat $vat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vat $vat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request)
    {
        $data = Vat::findOrFail($request->id);
        $data->deleted_id = $request->user()->id;
        $data->delete();
        return response()->json();
    }

}
