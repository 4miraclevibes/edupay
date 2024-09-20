<?php

namespace Database\Seeders;

use App\Models\Fee;
use App\Models\FeeDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payments')->insert([
            [
                'service_id' => 6,
                'user_id' => 4,
                'code' => 'TRX-1' . mt_rand(00000,99999),
                'total' => 1000,
                'subtotal' => FeeDetail::where('service_id', 6)->sum('price') + 1000,
                'status' => 'pending',
            ],
        ]);
    }
}
