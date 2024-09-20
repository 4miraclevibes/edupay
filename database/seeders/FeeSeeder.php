<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('fees')->insert([
            [
                'name' => 'Biaya pengembangan aplikasi',
                'price' => 1000,
                'user_id' => 6,
                'desc' => 'untuk bikin orang kesal',
            ],
            [
                'name' => 'Biaya pengembangan diri sendiri',
                'price' => 2000,
                'user_id' => 7,
                'desc' => 'untuk bikin orang kesal',
            ],
        ]);
    }
}
