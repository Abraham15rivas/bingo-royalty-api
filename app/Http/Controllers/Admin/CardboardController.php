<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;

class CardboardController extends Controller
{
    use ResponseTrait;

    public function index(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $result = 'lista de los cartones que pertenecen al usuario';
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($result, 'cardboard'));
    }
}
