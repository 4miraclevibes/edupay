<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->insert([
            [
                'name' => 'BCA VIRTUAL ACCOUNT',
                'code' => 'bca_va',
            ],
            [
                'name' => 'BRI VIRTUAL ACCOUNT',
                'code' => 'bri_va',
            ],
            [
                'name' => 'BNI VIRTUAL ACCOUNT',
                'code' => 'bni_va',
            ],
        ]);
    }
}
