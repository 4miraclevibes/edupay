<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transactions')->insert([
            [
                'transaction_code' => 'XXX' . mt_rand(000000,999999),
                'case' => 'topup',
                'status' => 'pending',
                'payment_id' => 1,
            ],
        ]);
        
        $data = Transaction::all();
        foreach($data as $item){
            TransactionDetail::create([
                'transaction_id' => $item->id,
                'user_id' => $item->payment->user->id,
                'status' => 'receiver',
            ]);
            TransactionDetail::create([
                'transaction_id' => $item->id,
                'user_id' => 1,
                'status' => 'sender',
            ]);
        }
        
    }
}
