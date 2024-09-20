<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'name' => 'ADMIN',
                'desc' => 'ADMIN SISTEM',
            ],
            [
                'name' => 'USER',
                'desc' => 'USER',
            ],
            [
                'name' => 'MERCHANT',
                'desc' => 'MERCHANT EDUBANK',
            ],
            [
                'name' => 'FEE ADMIN',
                'desc' => 'FEE COLLECTION EDUBANK',
            ],
            [
                'name' => 'FEE COLLECTOR',
                'desc' => 'FEE COLLECTION EDUBANK',
            ],
        ]);
    }
}
