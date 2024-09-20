<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FeeDetail;
use App\Models\Payment;
use App\Models\PaymentFee;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class PaymentController extends Controller
{
    public function index(){
        $data = Payment::with([
            'transaction', 
            'user', 
            'transaction.transactionDetail', 
            'transaction.transactionDetail.user', 
            'transaction.transactionDetail.user.wallet'
            ])->get();

        return response()->json([
            'data' => $data,
            'message' => 'Sukses',
            'code' => 200,
        ], 200);
    }

    public function show($id){
        $data = Payment::with(['user'])->where('code', $id)->first();
        return response()->json([
            'data' => $data,
            'message' => 'Sukses',
            'code' => 200,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'total' => 'required|numeric|min:5000',
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
            $feeDetails = FeeDetail::where('service_id', $request->service_id)->get();
            $feeTotal = $feeDetails->sum('price');
            $payment = Payment::create([
                'service_id' => $request->service_id,
                'user_id' => Auth::user()->id,
                'total' => $request->total,
                'subtotal' => $request->total + $feeTotal,
                'status' => 'pending',
            ]);

            
            foreach ($feeDetails as $feeDetail) {
                PaymentFee::create([
                    'payment_id' => $payment->id,
                    'name' => $feeDetail->fee->name,
                    'amount' => $feeDetail->fee->price,
                    'user_id' => $feeDetail->fee->user->id,
                ]);
            }

            $payment->update([
                'code' => 'TRX-' . $payment->id . mt_rand(00000,99999)
            ]);
            
            DB::commit();

            return response()->json([
                'data' => Payment::with(['paymentFee', 'user', 'service'])->find($payment->id),
                'message' => 'Sukses',
                'code' => 200,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data pembayaran',
                'code' => 500,
            ], 500);
        }
    }
    
    public function paymentSuccess(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:success,cancel',
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
            $item = Payment::with(['service.feeDetail.fee.user.wallet'])->find($id);
            $receiver = $item->user->id;
            $sender = Auth::user()->id;
            $senderWallet = Wallet::where('user_id', $sender)->first();
            $receiverWallet = Wallet::where('user_id', $receiver)->first();

            if ($senderWallet->pin == $request->pin && $senderWallet->balance >= $item->subtotal && $request->status == 'success' && $receiver !== $sender) {
                $item->update(['status' => $request->status]);

                $transaction = Transaction::create([
                    'payment_id' => $item->id,
                    'transaction_code' => 'TRX' . mt_rand(1000, 9999),
                    'status' => $request->status,
                    'total' => $item->subtotal,
                    'case' => 'payment',
                ]);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => $sender,
                    'amount' => $item->subtotal,
                    'case' => 'sender',
                    'status' => 'success',
                ]);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => $receiver,
                    'amount' => $item->total,
                    'case' => 'receiver',
                    'status' => 'success',
                ]);

                $senderWallet->update(['balance' => $senderWallet->balance - $item->subtotal]);
                $receiverWallet->update(['balance' => $receiverWallet->balance + $item->total]);

                foreach ($item->service->feeDetail as $detail) {
                    $wallet = $detail->fee->user->wallet;
                    $wallet->update(['balance' => $wallet->balance + $detail->price]);
                }

                DB::commit();

                // UPDATE PAYMENT STATUS BASED ON SERVICE
                if ($item->service) {
                    dd($item->service->name); 
                    $apiUrl = $this->getServiceApiUrl($item->service->name, $item->code);
                    
                    if ($apiUrl) {
                        $data = json_encode(['status' => 'success']);
                        
                        $options = [
                            'http' => [
                                'header'  => "Content-type: application/json\r\n" .
                                             "Accept: application/json\r\n",
                                'method'  => 'POST',
                                'content' => $data,
                                'timeout' => 30  // timeout dalam detik
                            ]
                        ];
                        
                        $context = stream_context_create($options);
                        try {
                            $result = file_get_contents($apiUrl, false, $context);
                            if ($result === FALSE) {
                                Log::error("Error updating {$item->service->name} payment status: Unable to reach the API");
                            } else {
                                $responseBody = json_decode($result, true);
                                Log::info("{$item->service->name} API response: ", ['body' => $responseBody]);
                            }
                        } catch (\Exception $e) {
                            Log::error("Error updating {$item->service->name} payment status: " . $e->getMessage());
                        }
                    }
                }

                return response()->json([
                    'data' => Payment::with(['transaction.transactionDetail', 'service.feeDetail.fee.user.wallet'])->find($item->id),
                    'message' => 'Sukses',
                    'code' => 200,
                ], 200);
            } elseif ($request->status == 'cancel') {
                return response()->json([
                    'message' => 'Transaksi dibatalkan',
                    'code' => 401,
                ], 401);
            } elseif($receiver == $sender) {
                return response()->json([
                    'message' => 'Tidak dapat melakukan transaksi pada diri sendiri',
                    'code' => 500,
                ], 500);
            } else {
                return response()->json([
                    'data' => $senderWallet->balance < $item->total ? 'Saldo tidak mencukupi' : 'Pin salah',
                    'message' => 'Transaksi gagal',
                    'code' => 500,
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses transaksi',
                'code' => 500,
            ], 500);
        }
    }

    public function paymentCancel($id)
    {
        $item = Payment::find($id);

        if (!$item) {
            return response()->json([
                'message' => 'Pembayaran tidak ditemukan',
                'code' => 404,
            ], 404);
        }

        $item->update(['status' => 'cancel']);

        return response()->json([
            'data' => $item,
            'message' => 'Pembayaran dibatalkan',
            'code' => 200,
        ], 200);
    }

    private function getServiceApiUrl($serviceName, $code)
    {
        switch ($serviceName) {
            case 'BALIAN':
                return "https://m.sod.my.id/api/payment/{$code}";
            case 'LAYANAN_LAIN':
                return "https://api.layanan-lain.com/update-payment/{$code}";
            // Tambahkan case lain untuk layanan lainnya
            default:
                Log::warning("No API URL defined for service: {$serviceName}");
                return null;
        }
    }
}
