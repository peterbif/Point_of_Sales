<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function list(Request $request)
    {
        // get param value
        $username = $request->username;
        $email = $request->email;

        $role = $request->role ?? 0;
        $sortBy = $request->sortBy ?? 'created_at';
        $orderBy = $request->orderBy ?? 'desc';
        try {
            $data = User::select('id', 'username', 'email', 'role', 'active', 'created_at')
                ->when($username, function ($query) use ($username) {
                    $query->where('username', 'like', '%' . $username . '%');
                })
                ->when($email, function ($query) use ($email) {
                    $query->where('email', 'like', '%' . $email . '%');
                })
                ->when($role, function ($query) use ($role) {
                    $query->where('role',  $role);
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
        $isUpdate = $request->id > 0;
    
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username,' . $request->id,
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                'unique:users,email,' . $request->id,
            ],
            'role' => ['required', 'string'],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'confirmed',
                'min:6', // adjust as needed
            ],
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
    
        try {
            DB::beginTransaction();
    
            if ($isUpdate) {
                $data = User::find($request->id);
    
                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found',
                    ], 404);
                }
            } else {
                $data = new User();
                $data->created_by_id = $request->user()->id;
            }
    
            $data->updated_by_id = $request->user()->id;
            $data->username = $request->username;
            $data->role = $request->role;
            $data->email = $request->email;
            $data->created_at = '2026-04-10 00:00:00';
            $data->updated_at = '2026-04-10 00:00:00';


    
            // safer boolean handling
            $data->active = filter_var($request->active, FILTER_VALIDATE_BOOLEAN);
    
            // update password only if provided
            if (!empty($request->password)) {
                $data->password = Hash::make($request->password);
            }
    
            $data->save();
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
    
        } catch (\Throwable $ex) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => config('app.debug') ? $ex->getMessage() : null,
            ], 500);
        }
    }
    public function edit(Request $request)
    {
        return response()->json(User::select('id', 'username', 'email', 'active', 'role')->findOrFail($request->id));
    }

    public function delete(Request $request)
    {
        $data = User::findOrFail($request->id);
        $data->deleted_id = $request->user()->id;
        $data->delete();
        return response()->json();
    }
}
