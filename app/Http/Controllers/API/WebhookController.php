<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TopUpTransaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function update()
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        $notif = new \Midtrans\Notification();

        $transactionStatus = $notif->transaction_status;
        $type = $notif->payment_type;
        $transactionCode = $notif->order_id;
        
        DB::beginTransaction();
        
        try {
            $status = null;

            if ($transactionStatus == 'capture'){
                if (fraudStatus == 'challenge'){
                    $status = 'challenge';
                } else if (fraudStatus == 'accept'){
                    $status = 'success';
                }
            } else if ($transactionStatus == 'settlement'){
                $status = 'success';
            } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire'){
                $status = 'failed';
            } else if ($transactionStatus == 'pending'){
                $status = 'pending';
            }

            $transaction = TopUpTransaction::where('code', $transactionCode)->first();

            if ($transaction->status !== 'success') {
                $transactionAmount = $transaction->amount;
                $userId = $transaction->user_id;

                $transaction->update(['status' => $status]);

                if ($status === 'success') {
                    Wallet::where('user_id', $userId)->increment('balance', $transactionAmount);
                }
            }

            DB::commit();
            return response()->json();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()]);
        }

    }
}
