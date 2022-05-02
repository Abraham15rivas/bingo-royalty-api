<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};

class AccountController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $accounts, $account;
    protected $user;
    protected $validatorRules = [];

    public function __construct() {
        $this->user = auth()->guard('api')->user();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $this->accounts = Account::select(
                'name',
                'description',
                'attributes',
                'type_account'
            )
            ->where('user_id', $this->user->id)
            ->get();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->accounts, 'accounts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        $this->validatorRules = [
            'name'          => 'string|required',
            'type_account'  => 'string|required',
            'description'   => 'string|nullable',
        ];

        $validator = $this->validator($request->all(), $this->validatorRules, class_basename($this));

        if ($validator->fails()) {
            return $this->validationFail($validator->errors());
        }

        DB::beginTransaction();

        try {
            $this->account = Account::create([
                'name'          => $request->name,
                'type_account'  => $request->typeAccount,
                'description'   => $request->description,
                'attributes'    => json_encode($request->detail),
                'user_id'       => $this->user->id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success([]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
