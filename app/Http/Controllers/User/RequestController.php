<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Request as RequestUser;
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};

class RequestController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $user;
    protected $requestUser;
    protected $validatorRules = [];

    public function __construct() {
        $this->user = auth()->guard('api')->user();
    }

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

    //    try {
            $this->requestUser = RequestUser::select(
                'reason',
                'image',
                'amount',
                'status',
                'type_request_id',
                'created_at',
            )
            ->where('user_id', $this->user->id)
            ->with('typeRequest')
            ->orderByDesc('created_at')
            ->get();

        // } catch (\Throwable $th) {
            // $statusCode = 1;
            // $msg = 'Hubo un error';
        // }
   
        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'requestUser' => isset($this->requestUser) ? $this->requestUser : null,
        ]);
    }

    public function store(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }
        
        $this->validatorRules = [
            'reason'        => 'string|required',
            'image'         => 'image|required',
            'description'   => 'string|required',
            'amount'        => 'required|numeric|regex:/^[\d]{0,11}(\.[\d]{1,2})?$/', /* 0 to 11 digits and 2 optional decimals */
        ];

        $validator = $this->validator($request->all(), $this->validatorRules, class_basename($this));

        if ($validator->fails()) {
            return $this->validationFail($validator->errors());
        }

        DB::beginTransaction();
        
        try {
            $this->requestUser                  = new RequestUser();
            $this->requestUser->reason          = $request->reason;
            $this->requestUser->description     = $request->description;
            $this->requestUser->amount          = $request->amount;
            $this->requestUser->user_id         = $this->user->id;
            $this->requestUser->status          = 'slope';
            $this->requestUser->type_request_id = 1;

            if ($request->file('image')) {
                $file_name = time().'-'.$this->requestUser->name.'-'.$request->file('image')->getClientOriginalName();
                $file_path = $request->file('image')->storeAs('reference', $file_name, 'public');
                $this->requestUser->image = '/storage/' . $file_path;
            }

            $this->requestUser->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success([]));
    }
}
