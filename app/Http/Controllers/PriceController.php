<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};
use App\Models\Price;

class PriceController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $rules;
    protected $user;
    protected $listPrice;

    protected $validatorRules = [

    ];

    public function __construct() {
        $this->user     = auth()->guard('api')->user();
        $this->rules    = config('bingo.rules');
    }

    public function index(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $this->listPrice = Price::select(
                'name',
                'description',
                'amount'    
            )
            ->get();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->listPrice, 'listPrice'));
    }
}
