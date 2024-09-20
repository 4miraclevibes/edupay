<?php

namespace App\Http\Middleware;

use App\Models\Wallet;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WalletStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $receiver = Auth::user()->transactionDetail->where('status', 'success')->where('case', 'receiver')->sum('amount');
        $sender = Auth::user()->transactionDetail->where('status', 'success')->where('case', 'sender')->sum('amount');
        $topUp = Auth::user()->topUpTransaction->where('status', 'success')->sum('amount');
        $wallet = Auth::user()->wallet->balance;
        $status = $wallet == $topUp + $receiver - $sender ? 'iya' : 'tidak';
        if($status == 'tidak' || Auth::user()->wallet->status == 'suspend'){
            if(Auth::user()->wallet !== 'suspend')
            Wallet::where('user_id', Auth::user()->id)->update([
                'status' => 'suspend'
            ]);
            return response()->json([
                'message' => 'suspended',
                'data' => Auth::user()->wallet,
            ]);
            // return redirect()->route('welcome');
        }
        return $next($request);
    }
}
