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
        $typeRequest = [
            ['Top up balance', 'Recarga de saldo'],
            ['Balance withdrawal', 'Retiro de saldo']
        ];

        foreach ($typeRequest as $type) {
            TypeRequest::create([
                'name'          => $type[0],
                'description'   => $type[1]
            ]);
        }
    }
}
