<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index(){
        $data = Transaction::where('payment_id', null)->with(['transactionDetail', 'transactionDetail.user', 'transactionDetail.user.wallet'])->get();
        return response()->json([
            'data' => $data == null ? 'kosong' : $data,
            'message' => 'success',
            'code' => 200,
        ], 200);
    }

    public function history(){
        $user = Auth::user()->id;
        $data = TransactionDetail::with(['transaction'])->where('user_id', $user)->where('status', 'sender')->get();
        return response()->json([
            'data' => $data,
            'message' => 'success',
            'code' => 200,
        ], 200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'receiver' => 'required|exists:users,id',
            'total' => 'required|numeric|min:0',
            'pin' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
                'code' => 422,
            ], 422);
        }

        DB::beginTransaction();

        try {
            $sender = Auth::user()->id;
            $wallet = Wallet::where('user_id', $sender)->first();
            $receiver = $request->receiver;
            $total = doubleval($request->total);
            $pin = $request->pin;
            $receiverWallet = Wallet::where('user_id', $receiver)->first();

            if ($pin === $wallet->pin && $wallet->balance >= $total && $wallet->id !== $receiverWallet->id) {
                $transaction = Transaction::create([
                    'transaction_code' => 'TRX' . mt_rand(1000, 9999),
                    'status' => 'success',
                    'case' => 'transaction',
                    'total' => $total,
                ]);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => $sender,
                    'amount' => $total,
                    'status' => 'success',
                    'case' => 'sender',
                ]);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => $receiver,
                    'amount' => $total,
                    'status' => 'success',
                    'case' => 'receiver',
                ]);

                $wallet->update([
                    'balance' => $wallet->balance - $total
                ]);

                $receiverWallet->update([
                    'balance' => $receiverWallet->balance + $total
                ]);

                DB::commit();

                return response()->json([
                    'data' => Transaction::with(['transactionDetail', 'transactionDetail.user', 'transactionDetail.user.wallet'])
                        ->find($transaction->id),
                    'message' => 'Sukses',
                    'code' => 200,
                ], 200);
            } else {
                $message = '';
                if ($wallet->balance <= $total) {
                    $message = 'Saldo tidak mencukupi';
                } else if ($pin !== $wallet->pin) {
                    $message = 'Pin salah';
                } else if ($wallet->id === $receiverWallet->id) {
                    $message = 'Tidak dapat mengirimkan ke diri sendiri';
                }
                return response()->json([
                    'message' => $message,
                    'code' => 401,
                ], 401);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses transaksi',
                'code' => 500,
            ], 500);
        }
    }
}
