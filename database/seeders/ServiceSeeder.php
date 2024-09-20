<?php

namespace Database\Seeders;

use App\Models\Fee;
use App\Models\FeeDetail;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('services')->insert([
            [
                'name' => 'CAFETARIA',
                'desc' => 'CAFETARIA UNP',
            ],
            [
                'name' => 'LAUNDRY',
                'desc' => 'LAUNDRY UNP',
            ],
            [
                'name' => 'CARWASH',
                'desc' => 'CARWASH UNP',
            ],
            [
                'name' => 'KOPERASI',
                'desc' => 'KOPERASI UNP',
            ],
            [
                'name' => 'FOTOCOPY',
                'desc' => 'FOTOCOPY UNP',
            ],
            [
                'name' => 'TOPUP',
                'desc' => 'EDUBANK',
            ],
            [
                'name' => 'WITHDRAW',
                'desc' => 'EDUBANK',
            ],
            [
                'name' => 'SOD',
                'desc' => 'SOD ARIF HIDROPONIK',
            ],
        ]);
        $data = Service::all();
        $fees = Fee::all();
        foreach ($data as $item){
            foreach ($fees as $fee){
                FeeDetail::create([
                    'service_id' => $item->id,
                    'fee_id' => $fee->id,
                    'price' => $fee->price,
                ]);
            }
        }
    }
}
