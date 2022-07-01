<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Rules\MatchOldPassword;
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};

class ChangePasswordController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $user;

    public function __construct() {
        $this->user  = auth()->guard('api')->user();
    }
     
    public function store(Request $request)
    {

        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        try {
            User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
        } catch (\Throwable $th) {
            $statusCode = 1;
            $msg = 'Hubo un error';
        }
   
        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
        ]);
    }
}
