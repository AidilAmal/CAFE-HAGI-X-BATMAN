<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cafe.test'],
            ['name' => 'Admin Cafe', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        User::updateOrCreate(
            ['email' => 'owner@cafe.test'],
            ['name' => 'Owner Cafe', 'password' => Hash::make('password'), 'role' => 'owner']
        );
    }
}
