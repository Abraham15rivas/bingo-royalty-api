<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Price;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Precio cartones normales
        Price::create([
            'name'          => 'price unitario',
            'description'   => 'precio unitario de cartón normal',
            'amount'        => 10,
            'price_type_id' => 1
        ]);

        // Precio cartones VIP
        Price::create([
            'name'          => 'price unitario VIP',
            'description'   => 'precio unitario de cartón VIP',
            'amount'        => 15,
            'price_type_id' => 2
        ]);

        // Precio suscripción VIP
        Price::create([
            'name'          => 'price suscripción VIP',
            'description'   => 'precio suscripción VIP',
            'amount'        => 20,
            'price_type_id' => 3
        ]);
    }
}
