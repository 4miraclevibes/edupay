<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\TopUpTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TopUpController extends Controller
{
    public function index(){
        $data = TopUpTransaction::all();
        return view('topUp.index',[
            'data' => $data
        ]);
    }

    public function store(Request $request){
        $data = $request->only('amount', 'pin', 'method');
        $pin = Auth::user()->wallet->pin;    
        $validator = Validator::make($data, [
            'amount' => 'required|integer|min:10000',
            'pin' => 'required|digits:6',
            'method' => 'required|in:bni_va,bca_va,bri_va'
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->messages()->first());
        }
        if($request->pin !== $pin){
            return back()->with('error', 'Pin kamu salah');
        }

        $paymentMethod = PaymentMethod::where('code', $request->method)->first();

        DB::beginTransaction();
        try {
            $transaction = TopUpTransaction::create([
                'user_id' => auth()->user()->id,
                'payment_method_id' => $paymentMethod->id,
                'code' => 'TRX-' .  mt_rand(00000,99999),
                'amount' => $request->amount,
            ]);

            $params = $this->buildMidtransParameter([
                'transaction_code' => $transaction->code,
                'amount' => $transaction->amount,
                'payment_method' => $paymentMethod->code
            ]);

            $midtrans = $this->callMidtrans($params);

            $transaction->update([
                'link' => $midtrans['redirect_url']
            ]);
            
            DB::commit();

            return back()->with('success', 'Top Up Berhasil');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()->with('error', 'Top Up Gagal');
        }
    }

    private function callMidtrans(array $params)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = (bool) env('MIDTRANS_IS_SANITIZED', true);
        \Midtrans\Config::$is3ds = (bool) env('MIDTRANS_IS_3DS', true);

        $createTransaction = \Midtrans\Snap::createTransaction($params);

        return [
            'redirect_url' => $createTransaction->redirect_url,
            'token' => $createTransaction->token
        ];
    }

    private function buildMidtransParameter(array $params)
    {
        $transactionDetails = [
            'order_id' => $params['transaction_code'],
            'gross_amount' => $params['amount']
        ];

        $user = auth()->user();
        $splitName = $this->splitName($user->name);
        $customerDetails = [
            'first_name' => $splitName['first_name'],
            'last_name' => $splitName['last_name'],
            'email' => $user->email
        ];

        $enabledPayments = [
            $params['payment_method']
        ];

        return [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'enabled_payments' => $enabledPayments
        ];
    }

    private function splitName($fullName)
    {
        $name = explode(' ', $fullName);
        
        $lastName = count($name)  > 1 ? array_pop($name) : $fullName;
        $firstName = implode(' ', $name);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName
        ];
    }
}
