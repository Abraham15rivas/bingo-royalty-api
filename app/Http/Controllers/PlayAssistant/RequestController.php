<?php

namespace App\Http\Controllers\PlayAssistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Request as RequestUser;

use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};

use App\Http\Controllers\User\WalletController;

class RequestController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $user;
    protected $request;
    protected $requests;
    protected $validatorRules = [];

    public function __construct() {
        $this->user = auth()->guard('api')->user();
    }

    public function index(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }
        
        try {
            $this->requests = RequestUser::select(
                'id',
                'reason',
                'description',
                'status',
                'user_id',
                'amount',
                'image'
            )
            ->where('status', 'slope')
            ->get();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->requests));
    }

    public function approveRequest(Request $request, $id) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        DB::beginTransaction();
        
        try {
            $this->request = RequestUser::select(
                'id',
                'status',
                'user_id',
                'amount',
            )
            ->where('id', $id)
            ->where('status', 'slope')
            ->first();

            if ($this->request) {
                $this->request->status = 'passed';
                $this->request->save();

                $request->offsetSet('balanceAcquired', $this->request->amount);

                $walletUser = new WalletController($this->request->user_id);
                $walletUser->rechargeBalance($request);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success([]));
    }

    public function rejectRequest(Request $request, $id) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        DB::beginTransaction();
        
        try {
            $this->request = RequestUser::select(
                'id',
                'status',
                'user_id',
                'amount',
            )
            ->where('id', $id)
            ->where('status', 'slope')
            ->first();

            if ($this->request) {
                $this->request->status = 'refused';
                $this->request->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success([]));
    }
}
