<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeRequest;

class TypeRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['Top up balance', 'Recarga de saldo'],
            ['Balance withdrawal', 'Retiro de saldo'],
            ['VIP subscription', 'Suscripción VIP']
        ];

        foreach ($roles as $role) {
            TypeRequest::create([
                'name'          => $role[0],
                'description'   => $role[1]
            ]);
        }
    }
}
