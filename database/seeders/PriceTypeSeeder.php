<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PriceType;

class PriceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $priceType = [
            ['price cardboard', 'precio de cartón normal'],
            ['price cardboard VIP', 'precio de cartón VIP'],
            ['VIP subscription', 'Suscripción VIP']
        ];

        foreach ($priceType as $type) {
            PriceType::create([
                'name'          => $type[0],
                'description'   => $type[1]
            ]);
        }
    }
}
