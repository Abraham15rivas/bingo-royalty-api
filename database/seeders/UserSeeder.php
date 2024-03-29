<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    User,
    Wallet,
    Profile
};

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        // Coleción de usuarios.
        $users = collect();
        // SuperAdmin
        $users->push(User::create([
            'name'          => 'MasterAdmin',
            'email'         => 'masteradmin@test.com',
            'password'      => Hash::make('secret123'),
            'referral_code' => User::getUniqueReferralCode(),
            'role_id'       => 1,
            'status'        => 'desconectado'
        ]));
    
        // Admin 
        $users->push(User::create([
            'name'          => 'Admin',
            'email'         => 'admin@test.com',
            'password'      => Hash::make('secret123'),
            'referral_code' => User::getUniqueReferralCode(),
            'role_id'       => 2,
            'status'        => 'desconectado'
        ]));
        
        // PlayAssistant
        $users->push(User::create([
            'name'          => 'PlayAssistant',
            'email'         => 'playassistant@test.com',
            'password'      => Hash::make('secret123'),
            'referral_code' => User::getUniqueReferralCode(),
            'role_id'       => 4,
            'status'        => 'desconectado'
        ]));
        
        // Supervisor
        $users->push(User::create([
            'name'          => 'Supervisor',
            'email'         => 'supervisor@test.com',
            'password'      => Hash::make('secret123'),
            'referral_code' => User::getUniqueReferralCode(),
            'role_id'       => 5,
            'status'        => 'desconectado'
        ]));

        // User
        $users->push(User::create([
            'name'          => 'Diego',
            'email'         => 'diego@test.com',
            'password'      => Hash::make('secret123'),
            'referral_code' => User::getUniqueReferralCode(),
            'role_id'       => 3,
            'status'        => 'desconectado'
        ]));
        
        // User
        $users->push(User::create([
            'name'          => 'Abraham',
            'email'         => 'abraham@test.com',
            'password'      => Hash::make('secret123'),
            'referral_code' => User::getUniqueReferralCode(),
            'role_id'       => 3,
            'status'        => 'desconectado'
        ]));

        // User
        $users->push(User::create([
            'name'          => 'jose',
            'email'         => 'jose@test.com',
            'vip'           => true,
            'password'      => Hash::make('secret123'),
            'referral_code' => User::getUniqueReferralCode(),
            'role_id'       => 3,
            'status'        => 'desconectado'
        ]));

        $this->createWalletForUsers($users);
        $this->createProfilesForUsers($users);
    }

    private function createWalletForUsers($users) {
        foreach ($users as $user) {
            Wallet::create([
                'name'      => 'Mi wallet',
                'balance'   => 0.00,
                'user_id'   => $user->id
            ]);
        }

        return true;
    }

    private function createProfilesForUsers($users) {
        foreach ($users as $user) {
            Profile::create([
                'name'              => $user->name,
                'profile_image'     => '/storage/profile/user.png',
                'user_id'           => $user->id
            ]);
        }
        return true;
    }
}
