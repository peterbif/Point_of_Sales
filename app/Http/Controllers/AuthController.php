<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class AuthController extends Controller
{
//     public function login(Request $request)
//     {
//         // // validation
      

//         $rules = [
//             'username' => 'nullable|required_without:email|string',
//             'email'    => 'nullable|required_without:username|email',
//             'password' => 'required|string',
//         ];

// $validator = Validator::make($request->all(), $rules);

// if ($validator->fails()) {
//     return response()->json([
//         'success' => false,
//         'errors' => $validator->errors()
//     ]);
// }

// // Determine if the user is logging in with a username or email
// $loginValue = $request->input('email') ?? $request->input('username');

// $loginField = filter_var($loginValue, FILTER_VALIDATE_EMAIL)
//     ? 'email'
//     : 'username';

// $credentials = [
//     $loginField => $loginValue,
//     'password'  => $request->password,
//     'active'    => 1,
// ];

// if (Auth::attempt($credentials)) {
//     $user = Auth::user();

//     $token = $user->createToken('YourAppName')->plainTextToken;

//     return response()->json([
//         'success' => true,
//         'token'   => $token,
//         'user'    => $user,
//     ]);
// }

// return response()->json([
//     'success' => false,
//     'errors'  => [
//         'login' => ['Incorrect username/email or password'],
//     ],
// ], 401);

//     }

  
// public function login(Request $request)
// {
//     // 1️⃣ Validate input
//     $rules = [
//         'username' => 'nullable|required_without:email|string',
//         'email'    => 'nullable|required_without:username|email',
//         'password' => 'required|string',
//     ];

//     $validator = Validator::make($request->all(), $rules);

//     if ($validator->fails()) {
//         return response()->json([
//             'success' => false,
//             'errors'  => $validator->errors()
//         ]);
//     }

//     // 2️⃣ Determine login field
//     $loginValue = $request->input('email') ?? $request->input('username');
//     $loginField = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

//     $credentials = [
//         $loginField => $loginValue,
//         'password'  => $request->password,
//         'active'    => 1,
//     ];

//     // 3️⃣ Attempt login
//     if (Auth::attempt($credentials)) {
//         $user = Auth::user();

//         // 4️⃣ Check subscription expiry
//         $expiryDate = $user->created_at->copy()->addYear(); // do not modify original
//         if (now()->greaterThan($expiryDate)) {
//             Auth::logout();

//             return response()->json([
//                 'success' => false,
//                 'errors' => [
//                     'subscription' => ['Account expired. Please renew subscription.']
//                 ]
//             ], 403);
//         }

//         // 5️⃣ Generate token
//         $token = $user->createToken('YourAppName')->plainTextToken;

//         return response()->json([
//             'success' => true,
//             'token'   => $token,
//             'user'    => $user,
//         ]);
//     }

//     // 6️⃣ Failed login
//     return response()->json([
//         'success' => false,
//         'errors'  => [
//             'login' => ['Incorrect username/email or password'],
//         ],
//     ], 401);
// }


public function login(Request $request)
{
    // 1️⃣ Validate input
    $rules = [
        'username' => 'required_without:email|nullable|string',
        'email'    => 'required_without:username|nullable|email',
        'password' => 'required|string',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    // 2️⃣ Determine login field
    $loginValue = $request->email ?: $request->username;
    $loginField = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    // ✅ IMPORTANT: Do NOT hash password here
    $credentials = [
        $loginField => $loginValue,
        'password'  => $request->password,
        'active'    => 1,
    ];

    // 3️⃣ Attempt login
    if (!Auth::attempt($credentials)) {
        return response()->json([
            'success' => false,
            'errors'  => [
                'login' => ['Incorrect username/email or password'],
            ],
        ], 401);
    }

    $user = Auth::user();

    // ✅ Superadmin (lifetime access: created_at = null)
    if ($user->role === 'superadmin2' && is_null($user->created_at)) {
        $token = $user->createToken('YourAppName')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
            'warning' => null,
        ]);
    }

    // ❗ Safety check (avoid crash if misconfigured)
    if (is_null($user->created_at)) {
        Auth::logout();

        return response()->json([
            'success' => false,
            'errors' => [
                'account' => ['Invalid account configuration. Contact admin.']
            ]
        ], 500);
    }

    // ✅ Normal users (1-year subscription)
    $expiryDate = $user->created_at->copy()->addYear();

    $now = now();
    $diffInSeconds = $now->diffInSeconds($expiryDate, false);

    $days  = floor($diffInSeconds / 86400);
    $hours = floor(($diffInSeconds % 86400) / 3600);

    $warning = null;

    if ($days <= 30) {
        if ($days > 0) {
            $warning = "⚠️ Your subscription will expire in {$days} day(s). Please renew soon.";
        } else {
            $warning = "⚠️ Your subscription will expire in {$hours} hour(s). Please renew immediately!";
        }
    }

    // ❌ Expired users
    if ($diffInSeconds < 0) {
        Auth::logout();

        return response()->json([
            'success' => false,
            'errors' => [
                'subscription' => ['Account expired. Please renew subscription.']
            ]
        ], 403);
    }

    // ✅ Generate token
    $token = $user->createToken('YourAppName')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'token'   => $token,
        'user'    => $user,
        'warning' => $warning,
    ]);
}




public function user(Request $request)
    {
        $data = $request->user()->only('id', 'username', 'role');
        $data['server_time'] = date('d-M-Y H:i:s');
        return response()->json($data);
    }


    public function getUser(Request $request)
    {
        $users = User::select('id','created_at', 'updated_at')
            ->latest()
            ->limit(1)
            ->get();
    
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function resetDate(Request $request)
    {

       // return  $request->reset_date;
        // ✅ Only superadmin can perform this
        if (auth()->user()->role !== 'superadmin2') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Carbon::parse($request->reset_date)

        // if (Carbon::parse($request->reset_date)->isFuture()) {
        //     return response()->json(['error' => 'Invalid date'], 422);
        // }
    
        $request->validate([
            'reset_date' => 'required|date',
        ]);

        if($request->reset_date){
    
        $updated = User::where('role', '!=', 'superadmin2')
            ->update([
                'created_at' => Carbon::parse($request->reset_date)
            ]);
        }
    
        return response()->json([
            'success' => true,
            'updated_count' => $updated
        ]);
    }

    public function logout(Request $request)
    {
        // Auth::logout();
        // $request->session()->invalidate();
        // // $request->session()->regenerateToken();
        // return response()->json(['success' => true]);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    // Change password
    public function changePassword(Request $request)
    {
        $rules = [
            'old_password' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if (!Hash::check($value, $request->user()->password)) {
                        $fail('The old password is incorrect');
                    }
                }
            ],
            'new_password' => ['required', 'confirmed'],
        ];
        //validate data
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);

        // change the password
        try {
            $user = $request->user();
            $user->updated_at = now();
            $user->updated_by_id = $user->id;
            $user->password = bcrypt($request->new_password);
            $user->save();

            $response['success'] = true;
            $response['data'] = null;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        return response()->json($response);
    }
}
