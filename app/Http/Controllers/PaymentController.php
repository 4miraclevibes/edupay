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

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Payment::with([
            'transaction' => ['transactionDetail'],
        ])->get();
        return view('payment.index', [
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function topUpIndex()
    {
        $data = Payment::where('user_id', Auth::user()->id)->with([
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
        ]);
        return back()->with('success', 'Top Up Berhasil');
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

                return back()->with('success', 'Transaksi berhasil');
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
}
