<?php
//mobile
namespace App\Http\Controllers\Api\V1\Auth;

use App\CentralLogics\Helpers;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class CustomerAuthController extends Controller
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {
            //auth()->user() is coming from laravel auth:api middleware
            $token = auth()->user()->createToken('PercetakanCustomerAuth')->accessToken;
            if (!auth()->user()->status) {
                $errors = [];
                array_push($errors, ['code' => 'auth-003', 'message' => trans('messages.your_account_is_blocked')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }

            // Check user role and set appropriate redirect URL
            if (auth()->user()->hasRole('user')) {
                return response()->json([
                    'token' => $token, 
                    'is_phone_verified' => auth()->user()->is_phone_verified,
                    'redirect_url' => '/tokodashboard'
                ], 200);
            } elseif (auth()->user()->hasRole('admin')) {
                return response()->json([
                    'token' => $token,
                    'is_phone_verified' => auth()->user()->is_phone_verified,
                    'redirect_url' => '/admin/dashboard'
                ], 200);
            }

            return response()->json(['token' => $token, 'is_phone_verified' => auth()->user()->is_phone_verified], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Email atau password salah.']);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|min:3',
            //'l_name' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|unique:users|min:11',
            'password' => 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
        ],
        [
            'f_name.required' => 'Nama tidak boleh kurang dari 3 huruf.',
            'phone.required' => 'Nomor telepon telah diambil.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user = User::create([
            'f_name' => $request->f_name,
            //'l_name' => $request->l_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'email_verified_at' => now()
        ]);
        $user->addRole('user');
        $token = $user->createToken('RestaurantCustomerAuth')->accessToken;

        return response()->json(['token' => $token, 'is_phone_verified' => 0, 'phone_verify_end_url' => "api/v1/auth/verify-phone"], 200);
    }
}
