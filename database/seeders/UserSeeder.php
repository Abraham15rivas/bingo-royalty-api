<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // SuperAdmin
        User::create([
            'name' => 'SuperAdmin',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('secret123'),
            'role_id' => 1
        ]);
        // Admin 
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
            'role_id' => 2
        ]);
        // user
        User::create([
            'name' => 'Diego',
            'email' => 'diego@test.com',
            'password' => Hash::make('secret123'),
            'role_id' => 3
        ]);
        // user
        User::create([
            'name' => 'Abraham',
            'email' => 'abraham@test.com',
            'password' => Hash::make('secret123'),
            'role_id' => 3
        ]);
    }
}
