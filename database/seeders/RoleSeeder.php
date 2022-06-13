<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['MasterAdmin', 'Master Admin'],
            ['Admin', 'Administrador'],
            ['User', 'Usuarios jugadores'],
            ['PlayAssistant', 'Asistente de jugada'],
            ['Supervisor', 'Supervisor']
        ];

        foreach ($roles as $role) {
            Role::create([
                'name'          => $role[0],
                'description'   => $role[1]
            ]);
        }
    }
}
