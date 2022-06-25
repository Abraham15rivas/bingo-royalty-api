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

    public function createMeeting(Request $request) {

    }    
}
