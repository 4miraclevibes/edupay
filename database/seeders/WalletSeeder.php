<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = User::all();
        foreach($data as $item){
            Wallet::create([
                'balance' => 0,
                'pin' => '123456',
                'status' => 'normal',
                'user_id' => $item->id,
            ]);
        }
    }
}
