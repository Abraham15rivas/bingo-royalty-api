<?php

namespace App\Http\Controllers\PlayAssistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};
class GameController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $rules;
    protected $user;

    protected $validatorRules = [

    ];

    public function __construct() {
        $this->user     = auth()->guard('api')->user();
        $this->rules    = config('bingo.rules');
    }

    public function createMeeting(Request $request) {

    }

    public function throwNumber(Request $request) {
        
    }
}