<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\{
    DB,
    Validator
};
use App\Models\Wallet;

class WalletController extends Controller
{
    use ResponseTrait;

    protected $user;

    protected $validationRules = [
        'balanceAcquired' => 'required|numeric|regex:/^[\d]{0,11}(\.[\d]{1,2})?$/', /* 0 to 11 digits and 2 optional decimals */
    ];

    public function __construct() {
        $this->user = auth()->guard('api')->user();
    }

    public function index(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $wallet = Wallet::select(
                'name',
                'balance',
            )
            ->where('user_id', $this->user->id)
            ->first();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($wallet, 'wallet'));
    }

    public function rechargeBalance(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        $validator = Validator::make($request->all(), $this->validationRules);

        if ($validator->fails()) {
            return response()->json($this->validationFail($validator->errors()));
        }

        try {
            if ($request->balanceAcquired > 0) {
                $wallet = Wallet::select(
                    'id',
                    'name',
                    'balance',
                )
                ->where('user_id', $this->user->id)
                ->first();

                if ($wallet) {
                    $wallet->balance += $request->balanceAcquired;
                    $wallet->save();

                    $result = 'Recarga exitosa';
                }
            } else {
                $result = 'Algo salió mal';
            }
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($result));

    }

    public function transferBalance(Request $request) {

    }

    public function withdrawalOfFunds(Request $request) {
        
    }

    public function getActivities(Request $request) {
    
    }
}
