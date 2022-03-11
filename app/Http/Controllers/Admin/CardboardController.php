<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;

class CardboardController extends Controller
{
    use ResponseTrait;

    protected $rules = [
        'letters'   => [
            'B' => [1, 15],
            'I' => [16, 30],
            'N' => [31, 45],
            'G' => [46, 60],
            'O' => [61, 75]
        ],
        'rowNumber' => 5
    ];

    public function index(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $result = $this->cardboardGenerator();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($result, 'cardboard'));
    }

    private function cardboardGenerator() {
        $matrix = collect();

        try {
            foreach ($this->rules['letters'] as $key => $item) {
                $matrix[$key] = collect();

                do {
                    $lastRandomInt  = 0;
                    $randomInt      = random_int($item[0], $item[1]);

                    if ($randomInt >= $item[0] && $randomInt <= $item[1]) {
                        if ($lastRandomInt !== $randomInt) {
                            $matrix[$key]->push($randomInt);
                            $lastRandomInt = $randomInt;
                        }
                    }
                } while ($this->rules['rowNumber'] > $matrix[$key]->count());
            }
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return $matrix;
    }
}
