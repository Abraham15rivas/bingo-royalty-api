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
                'id',
                'name',
                'description',
                'is_active',
                'attributes',
                'type_account'
            )
            ->where('user_id', $this->user->id)
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name'         => 'string|required',
            'type_account' => 'string|required',
            'description'  => 'string|nullable',
            'detail'   => 'required'
        ]);

        try {
            $profile = Account::where('user_id', $request->user()->id)->get();
            $profile = $profile[0];
            
            $profile->name = $request->name;
            $profile->last_name = $request->last_name;
            $profile->nick_name = $request->nick_name;
            $profile->country = $request->country;

            $profile->save();

        } catch (\Throwable $th) {
            $statusCode = 1;
            $msg = 'Hubo un error';
        }

        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'user' => isset($profile) ? $profile : (object) []
        ]);
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

    public function show(Request $request, $id)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }        

        try {
            $accountActive = Account::select(
                'id',
                'name',
                'is_active',
                'type_account',
                'attributes'
            )
            ->where('user_id', $this->user->id)
            ->where('is_active', 1)
            ->first();

        } catch (\Throwable $th) {
            $statusCode = 1;
            $msg = 'Hubo un error';
        }

        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'account' => isset($accountActive) ? $accountActive : $accountActive
        ]);
    }

    public function activeAccount(Request $request, $id)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }        

        $validatedData = $request->validate([
            'is_active' => 'boolean|required',
        ]);

        try {
            if ($this->user->role_id == 2) {
                $account = Account::where('id', $id)
                    ->where('user_id', $this->user->id)
                    ->update(['is_active' => $request->is_active]);
            } else {
                $accountDeactive = Account::select('is_active')
                ->where('user_id', $this->user->id)
                ->where('is_active', 1)
                ->update(array(
                    'is_active' => 0,
                ));

                $account = Account::where('id', $id)
                    ->where('user_id', $this->user->id)
                    ->update(['is_active' => $request->is_active]);
            }
               
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
