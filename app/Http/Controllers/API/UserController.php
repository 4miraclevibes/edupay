<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;
use App\Models\User;

class UserController extends Controller
{
    public function index(){
        $users = User::with('role')->get();
        return response()->json([
            'data' => $users,
            'message' => 'success',
            'code' => 200
        ], 200);
    }
    
    public function show(){
        $user = User::with('wallet')->find(Auth::user()->id);
        return response()->json([
            'data' => $user,
            'message' => 'success',
            'code' => 200
        ], 200);
    }
    public function fetch(Request $request)
    {
        return response()->json([
            'data' => $request->user(),
            'message' => 'Data profile user berhasil diambil',
            'code' => 200,
        ], 200);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'code' => 200,
                ], 200);
            }

            $user = User::where('email', $request->email)->with(['wallet', 'role'])->first();
            if ( ! Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return response()->json([
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user,
                    'message' => 'Authenticated',
                    'code' => 200
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                    'message' => 'Something went wrong',
                    'error' => $error,
                    'status' => 'Authentication Failed',
                    'code' => 500,
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
                'role_id' => ['required', 'exists:roles,id'],
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user,
                    'message' => 'User Registered',
                    'code' => 200,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                    'message' => 'Something went wrong',
                    'error' => $error,
                    'status' => 'Authentication Failed',
                    'code' => 500,
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => $token,
            'mesage' => 'Token Revoked, Berhasil Logout',
            'code' => 200
        ], 200);
    }

    public function detail(){
        $user = User::where('id', Auth::user()->id)->first();
        $tokenResult = $user->createToken('authToken')->plainTextToken;
        return response()->json([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
                'status' => 'Success',
                'code' => 200
        ], 200);
    }
}
