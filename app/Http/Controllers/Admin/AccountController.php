<?php

namespace App\Http\Controllers\Admin;

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
            $this->accounts = Account::where('user_id', $this->user->id)
                ->orderBy('is_active', 'DESC')
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
            'name'         => 'string|required',
            'type_account' => 'string|required',
            'description'  => 'string|nullable',
            'detail'   => 'required'
        ];

        $validator = $this->validator($request->all(), $this->validatorRules, class_basename($this));

        if ($validator->fails()) {
            return $this->validationFail($validator->errors());
        }
        DB::beginTransaction();
        
        try {
            $this->account = Account::create([
                'name'          => $request->name,
                'type_account'  => $request->type_account,
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
     * Update a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }


        $this->validatorRules = [
            'name'        => 'string|required',
            'description' => 'string|nullable',
            'detail'   => 'required'
        ];

        $validator = $this->validator($request->all(), $this->validatorRules, class_basename($this));

        if ($validator->fails()) {
            return $this->validationFail($validator->errors());
        }
        DB::beginTransaction();
        
        try {
            $this->account = Account::findOrFail($id);

            $attributes = json_decode($this->account->attributes);
            $attributes = $request->detail;
            $this->account->update([
                'name'        => $request->name,
                'description' => $request->description,
                'type_account' => $request->type_account,
                'attributes'  => json_encode($attributes),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success([]));
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {   
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        $account = Account::where('id',$id)->delete();

        if($account > 0 ){
            return response()->json(['message'=>'Eliminado correctamente'], 200);
        }
    }

    public function activeAccount(Request $request, $id)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }        

        try {
            $account = Account::where('id', $id)
                ->where('user_id', $this->user->id)
                ->first();

            $account->is_active = !$account->is_active;
            $account->save();
            
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
