<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\Service;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index(){
        $services = Service::all();
        return response()->json([
            'data' => $services,
            'message' => 'success',
            'code' => 200,
        ], 200);
    }

    public function storePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'total' => 'required|numeric|min:0',
            'code' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Akun Merchant Belum Terdaftar',
                'code' => 404,
            ], 404);
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first(),
                'code' => 422,
            ], 422);
        }

        Payment::create([
            'service_id' => $request->service_id,
            'user_id' => $user->id,
            'total' => $request->total,
            'subtotal' => $request->total,
            'status' => 'pending',
            'code' => $request->code,
        ]);
        return response()->json([
            'message' => 'Berhasil membuat transaksi',
            'code' => 200,
        ], 200);
    }
}


