<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};
use App\Models\User;

class GamersController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $user;

    public function __construct() {
        $this->user  = auth()->guard('api')->user();
    }

    public function index(Request $request)
    {
        // if (!$request->ajax()) {
        //     return response()->json($this->invalidRequest());
        // }

        try {
            $gamers = User::where('role_id', '=', 3)->get();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }
        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'gamers' => isset($gamers) ? $gamers : $gamers
        ]);
    }
}


