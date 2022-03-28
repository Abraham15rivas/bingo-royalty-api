<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait SeriateTrait {
    public function generateSeries() {
        $stringRamdon = Str::upper(Str::random(10));
        return $stringRamdon;
    }
}