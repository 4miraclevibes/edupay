<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class WalletController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pin' => 'required|min:6|max:6',
        ]);
        Wallet::create([
            'pin' => $request->pin,
            'user_id' => Auth::user()->id,
        ]);
        return back()->with('success', 'Wallet created successfully');
    }
}
