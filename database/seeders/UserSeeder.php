<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'admin',
                'role_id' => 1,
                'email' => 'admin@example.com',
                'password' => Hash::make('password')
            ],
            [
                'name' => 'user1',
                'role_id' => 2,
                'email' => 'user1@example.com',
                'password' => Hash::make('password')
            ],
            [
                'name' => 'user2',
                'role_id' => 2,
                'email' => 'user2@example.com',
                'password' => Hash::make('password')
            ],
            [
                'name' => 'balian',
                'role_id' => 3,
                'email' => 'balian@example.com',
                'password' => Hash::make('password')
            ],
            [
                'name' => 'cafetaria',
                'role_id' => 3,
                'email' => 'cafetaria@example.com',
                'password' => Hash::make('password')
            ],
            [
                'name' => 'kholid hasibuan',
                'role_id' => 4,
                'email' => 'kholid.hasibuan35@gmail.com',
                'password' => Hash::make('password')
            ],
            [
                'name' => 'edubank fee collector',
                'role_id' => 5,
                'email' => 'fee.collector@example.com',
                'password' => Hash::make('password')
            ],
        ]);
    }
}
