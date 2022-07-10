<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};
use App\Models\{
    Wallet,
    User
};

class WalletController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $user;
    protected $userId;

    protected $validationRules = [
        'balanceAcquired' => 'required|numeric|regex:/^[\d]{0,11}(\.[\d]{1,2})?$/', /* 0 to 11 digits and 2 optional decimals */
    ];

    public function __construct($userId = null) {
        if ($userId === null) {
            $this->user = auth()->guard('api')->user();
            $this->userId = $this->user->id;
        } else {
            $this->userId = $userId;
        }
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

        $validator = $this->validator($request->all(), $this->validationRules, class_basename($this));

        if ($validator->fails()) {
            return response()->json($this->validationFail($validator->errors()));
        }

        DB::beginTransaction();

        try {
            if ($request->balanceAcquired > 0) {
                $wallet = Wallet::select(
                    'id',
                    'name',
                    'balance',
                )
                ->where('user_id', $this->userId)
                ->first();

                if ($wallet) {
                    $wallet->balance += $request->balanceAcquired;
                    $wallet->save();

                    $result = 'Recarga exitosa';
                }
            } else {
                $result = 'Algo salió mal';
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($result));
    }

    public function transferBalance(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        $validationRules = [
            'balanceToSend' => 'required|numeric|regex:/^[\d]{0,11}(\.[\d]{1,2})?$/', /* 0 to 11 digits and 2 optional decimals */
            'email'         => 'required|email'
        ];

        $validator = $this->validator($request->all(), $validationRules, class_basename($this));

        if ($validator->fails()) {
            return response()->json($this->validationFail($validator->errors()));
        }

        DB::beginTransaction();

        try {
            if ($request->balanceToSend > 0) {
                $sender     = $this->user->wallet;
                $balance    = $sender->balance;

                // Verificar condiciones
                if ($balance > 0 && $balance >= $request->balanceToSend) { 
                    $receiver = User::with([
                            'wallet' => function ($query) {
                                $query->select('id', 'user_id', 'balance');
                            }
                        ])
                        ->select('id', 'email')
                        ->where('email', $request->email)
                        ->where('role_id', 3)
                        ->where('id', '!=', $this->user->id)
                        ->first();

                    if (!empty($receiver)) {
                        $sender->update([
                            'balance' => ($sender->balance - $request->balanceToSend)
                        ]);

                        $receiver->wallet->update([
                            'balance' => ($receiver->wallet->balance + $request->balanceToSend)
                        ]);
                    } else {
                        $errors = $this->customValidator(class_basename($this), 'email', 'no registrado.');
                        return response()->json($this->validationFail($errors));
                    }
                } else {
                    $errors = $this->customValidator(class_basename($this), 'balanceToSend', 'fondos insuficientes.');
                    return response()->json($this->validationFail($errors));
                }
            } else {
                $errors = $this->customValidator(class_basename($this), 'balanceToSend', 'debe ser mayor a 0.');
                return response()->json($this->validationFail($errors));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($sender->only('name', 'balance')));
    }

    public function withdrawalOfFunds(Request $request) {
        // Code
    }

    public function getActivities(Request $request) {
        // Code
    }
}
