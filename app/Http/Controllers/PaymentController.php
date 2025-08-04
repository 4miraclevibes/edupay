<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentFee;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::user()->role->name == 'ADMIN'){
            $data = TransactionDetail::with([
                'transaction' => ['payment' => ['service', 'user']],
                'user'
            ])->get();
        }else{
            $data = TransactionDetail::where('user_id', Auth::user()->id)->with([
                'transaction' => ['payment' => ['service', 'user']],
                'user'
            ])->get();
        }
        return view('payment.index', [
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function topUpIndex()
    {
        $data = Payment::where('service_id', 6)->where('user_id', Auth::user()->id)->with([
            'transaction' => ['transactionDetail'],
        ])->get();
        return view('payment.index', [
            'data' => $data
        ]);
    }

    public function topUpUser(Request $request)
    {
        $service = Service::where('name', 'TOPUP')->first();
        $fee = $service->feeDetail->sum('price');
        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->messages()->first());
        }

        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'service_id' => $service->id,
                'user_id' => Auth::user()->id,
                'total' => $request->total,
                'subtotal' => $request->total + $fee,
                'status' => 'pending',
            ]);

            foreach ($service->feeDetail as $detail) {
                PaymentFee::create([
                    'payment_id' => $payment->id,
                    'name' => $detail->fee->name,
                    'amount' => $detail->fee->price,
                    'user_id' => $detail->fee->user->id,
                ]);
            }

            $payment->update([
                'code' => 'TRX-' . $payment->id . mt_rand(00000,99999)
            ]);

            DB::commit();

            return back()->with('success', 'Top Up Berhasil');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'total' => 'required|numeric|min:0',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->messages()->first());
        }

        Payment::create([
            'service_id' => $request->service_id,
            'user_id' => Auth::user()->id,
            'total' => $request->total,
            'subtotal' => $request->total,
            'status' => 'pending',
            'code' => $request->code,
        ]);
        return back()->with('success', 'Berhasil membuat transaksi');
    }

    public function paymentSuccess(Request $request)
    {
        $status = 'success';
        $validator = Validator::make($request->all(), [
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
            $item = Payment::with(['service.feeDetail.fee.user.wallet'])->where('code', $request->code)->first();
            $receiver = $item->user->id;
            $sender = Auth::user()->id;
            $senderWallet = Wallet::where('user_id', $sender)->first();
            $receiverWallet = Wallet::where('user_id', $receiver)->first();

            if ($senderWallet->pin == $request->pin && $senderWallet->balance >= $item->subtotal && $status == 'success' && $receiver !== $sender) {
                $item->update(['status' => $status]);

                $transaction = Transaction::create([
                    'payment_id' => $item->id,
                    'transaction_code' => 'TRX' . mt_rand(1000, 9999),
                    'status' => $status,
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
                $serviceUpdateStatus = null;
                if ($item->service) {
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
                            // Log request yang akan dikirim
                            Log::info("Mengirim request ke service API", [
                                'payment_id' => $item->id,
                                'service' => $item->service->name,
                                'code' => $item->code,
                                'api_url' => $apiUrl,
                                'request_data' => $data
                            ]);

                            $result = file_get_contents($apiUrl, false, $context);
                            if ($result === FALSE) {
                                Log::error("Error updating {$item->service->name} payment status: Unable to reach the API", [
                                    'payment_id' => $item->id,
                                    'service' => $item->service->name,
                                    'code' => $item->code,
                                    'api_url' => $apiUrl
                                ]);
                                $serviceUpdateStatus = [
                                    'success' => false,
                                    'message' => "Gagal menghubungi API {$item->service->name}",
                                    'service' => $item->service->name
                                ];
                            } else {
                                $responseBody = json_decode($result, true);
                                Log::info("{$item->service->name} API response: ", [
                                    'payment_id' => $item->id,
                                    'service' => $item->service->name,
                                    'code' => $item->code,
                                    'raw_response' => $result,
                                    'parsed_response' => $responseBody
                                ]);

                                // Cek apakah response berhasil
                                // Response yang diharapkan: {"message": "Payment notification received", "payment_status": "success", ...}
                                if ($responseBody &&
                                    isset($responseBody['message']) &&
                                    $responseBody['message'] === 'Payment notification received' &&
                                    isset($responseBody['payment_status']) &&
                                    $responseBody['payment_status'] === 'success') {
                                    $serviceUpdateStatus = [
                                        'success' => true,
                                        'message' => "Berhasil update status ke {$item->service->name}",
                                        'service' => $item->service->name,
                                        'response' => $responseBody
                                    ];
                                    Log::info("Service update berhasil", [
                                        'payment_id' => $item->id,
                                        'service' => $item->service->name,
                                        'code' => $item->code,
                                        'response' => $responseBody
                                    ]);
                                } else {
                                    $serviceUpdateStatus = [
                                        'success' => false,
                                        'message' => "Response tidak valid dari {$item->service->name}",
                                        'service' => $item->service->name,
                                        'response' => $responseBody
                                    ];
                                    Log::warning("Service update gagal - response tidak valid", [
                                        'payment_id' => $item->id,
                                        'service' => $item->service->name,
                                        'code' => $item->code,
                                        'response' => $responseBody
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error("Error updating {$item->service->name} payment status: " . $e->getMessage(), [
                                'payment_id' => $item->id,
                                'service' => $item->service->name,
                                'code' => $item->code,
                                'api_url' => $apiUrl,
                                'error' => $e->getMessage()
                            ]);
                            $serviceUpdateStatus = [
                                'success' => false,
                                'message' => "Error: " . $e->getMessage(),
                                'service' => $item->service->name
                            ];
                        }
                    } else {
                        Log::warning("URL API tidak ditemukan untuk service", [
                            'payment_id' => $item->id,
                            'service' => $item->service->name,
                            'code' => $item->code
                        ]);
                        $serviceUpdateStatus = [
                            'success' => false,
                            'message' => "URL API tidak ditemukan untuk service {$item->service->name}",
                            'service' => $item->service->name
                        ];
                    }
                } else {
                    Log::info("Tidak ada service yang terkait dengan transaksi", [
                        'payment_id' => $item->id,
                        'code' => $item->code
                    ]);
                    $serviceUpdateStatus = [
                        'success' => false,
                        'message' => "Tidak ada service yang terkait dengan transaksi ini",
                        'service' => null
                    ];
                }

                // Tampilkan pesan berdasarkan status service update
                if ($serviceUpdateStatus['success']) {
                    return back()->with('success', 'Transaksi berhasil. ' . $serviceUpdateStatus['message']);
                } else {
                    return back()->with('warning', 'Transaksi berhasil, namun: ' . $serviceUpdateStatus['message']);
                }
            } elseif ($status == 'cancel') {
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

    public function paymentCancel($id){
        $item = Payment::find($id);
        $item->update([
            'status' => 'cancel',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $status = 'success';
        $item = Payment::find($id);
        $item->update([
            'status' => $status,
        ]);
        dd($item->status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function getServiceApiUrl($serviceName, $code)
    {
        switch ($serviceName) {
            case 'BALIAN':
                return "https://m.sod.my.id/api/payment-notification/{$code}";
            case 'EDEPOT':
                return "https://edepot.justputoff.com/api/payment-notification/{$code}";
            case 'SPORTLODEK':
                return "https://sportlodek.justputoff.com/api/payment-notification/{$code}";
            case 'CAFETARIA':
                return "https://cafetaria.justputoff.com/api/payment-notification/{$code}";
            // Tambahkan case lain untuk layanan lainnya
            default:
                Log::warning("No API URL defined for service: {$serviceName}");
                return null;
        }
    }

    /**
     * Get detailed service update status for debugging/monitoring
     */
    public function getServiceUpdateStatus($paymentId)
    {
        // Jika request ingin JSON response
        if (request()->wantsJson()) {
            return $this->getServiceUpdateStatusJson($paymentId);
        }

        // Jika request ingin view
        $payment = Payment::with('service')->find($paymentId);

        if (!$payment) {
            return back()->with('error', 'Payment tidak ditemukan');
        }

        if (!$payment->service) {
            $serviceStatus = [
                'success' => false,
                'message' => 'Tidak ada service yang terkait dengan payment ini',
                'payment_id' => $paymentId,
                'service' => null
            ];
        } else {
            $apiUrl = $this->getServiceApiUrl($payment->service->name, $payment->code);

            if (!$apiUrl) {
                $serviceStatus = [
                    'success' => false,
                    'message' => "URL API tidak ditemukan untuk service {$payment->service->name}",
                    'payment_id' => $paymentId,
                    'service' => $payment->service->name,
                    'api_url' => null
                ];
            } else {
                // Test koneksi ke API
                $data = json_encode(['status' => 'test']);
                $options = [
                    'http' => [
                        'header'  => "Content-type: application/json\r\n" .
                                     "Accept: application/json\r\n",
                        'method'  => 'POST',
                        'content' => $data,
                        'timeout' => 10
                    ]
                ];

                $context = stream_context_create($options);

                try {
                    $result = file_get_contents($apiUrl, false, $context);
                    if ($result === FALSE) {
                        $serviceStatus = [
                            'success' => false,
                            'message' => "Gagal menghubungi API {$payment->service->name}",
                            'payment_id' => $paymentId,
                            'service' => $payment->service->name,
                            'api_url' => $apiUrl,
                            'error' => 'Connection failed'
                        ];
                    } else {
                        $responseBody = json_decode($result, true);
                        $serviceStatus = [
                            'success' => true,
                            'message' => "API {$payment->service->name} dapat diakses",
                            'payment_id' => $paymentId,
                            'service' => $payment->service->name,
                            'api_url' => $apiUrl,
                            'response' => $responseBody,
                            'response_status' => $responseBody && isset($responseBody['status']) ? $responseBody['status'] : 'unknown'
                        ];
                    }
                } catch (\Exception $e) {
                    $serviceStatus = [
                        'success' => false,
                        'message' => "Error saat mengakses API: " . $e->getMessage(),
                        'payment_id' => $paymentId,
                        'service' => $payment->service->name,
                        'api_url' => $apiUrl,
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        return view('payment.service-status', compact('serviceStatus'));
    }

    /**
     * Get service update status as JSON response
     */
    private function getServiceUpdateStatusJson($paymentId)
    {
        $payment = Payment::with('service')->find($paymentId);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment tidak ditemukan'
            ], 404);
        }

        if (!$payment->service) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada service yang terkait dengan payment ini',
                'payment_id' => $paymentId,
                'service' => null
            ]);
        }

        $apiUrl = $this->getServiceApiUrl($payment->service->name, $payment->code);

        if (!$apiUrl) {
            return response()->json([
                'success' => false,
                'message' => "URL API tidak ditemukan untuk service {$payment->service->name}",
                'payment_id' => $paymentId,
                'service' => $payment->service->name,
                'api_url' => null
            ]);
        }

        // Test koneksi ke API
        $data = json_encode(['status' => 'test']);
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n" .
                             "Accept: application/json\r\n",
                'method'  => 'POST',
                'content' => $data,
                'timeout' => 10
            ]
        ];

        $context = stream_context_create($options);

        try {
            $result = file_get_contents($apiUrl, false, $context);
            if ($result === FALSE) {
                return response()->json([
                    'success' => false,
                    'message' => "Gagal menghubungi API {$payment->service->name}",
                    'payment_id' => $paymentId,
                    'service' => $payment->service->name,
                    'api_url' => $apiUrl,
                    'error' => 'Connection failed'
                ]);
            } else {
                $responseBody = json_decode($result, true);
                // Validasi response sesuai format yang diharapkan
                $isValidResponse = $responseBody &&
                    isset($responseBody['message']) &&
                    $responseBody['message'] === 'Payment notification received' &&
                    isset($responseBody['payment_status']) &&
                    $responseBody['payment_status'] === 'success';

                return response()->json([
                    'success' => $isValidResponse,
                    'message' => $isValidResponse ? "API {$payment->service->name} dapat diakses dan response valid" : "API {$payment->service->name} dapat diakses tapi response tidak sesuai format",
                    'payment_id' => $paymentId,
                    'service' => $payment->service->name,
                    'api_url' => $apiUrl,
                    'response' => $responseBody,
                    'response_valid' => $isValidResponse,
                    'expected_format' => [
                        'message' => 'Payment notification received',
                        'payment_status' => 'success'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error saat mengakses API: " . $e->getMessage(),
                'payment_id' => $paymentId,
                'service' => $payment->service->name,
                'api_url' => $apiUrl,
                'error' => $e->getMessage()
            ]);
        }
    }
}
