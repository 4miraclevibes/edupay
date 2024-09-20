<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\PaymentFee;
use App\Models\User;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index(){
        $data = Fee::all();
        return view('fee.index', [
            'data' => $data
        ]);
    }
    
    public function feeHistory(){
        $data = PaymentFee::all();
        return view('fee.feeHistory', [
            'data' => $data
        ]);
    }

    public function create(){
        $users = User::whereNotIn('role_id', [1,2,3])->get();
        return view('fee.create', [
            'data' => $users
        ]);
    }

    public function store(Request $request){
        $data = $request->all();
        Fee::create($data);
        return redirect()->route('fee.index')->with('success', 'SUKSESS');
    }
}
