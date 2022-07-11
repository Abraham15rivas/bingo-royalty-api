<?php

namespace App\Http\Controllers\PlayAssistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};
use App\Models\Meeting;
class GameController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $rules;
    protected $user;
    protected $meeting;

    protected $validatorRules = [
        'name'              => 'required|string',
        'cardboard_number'  => 'required|integer',
        'line_play'         => 'required|numeric|regex:/^[\d]{0,11}(\.[\d]{1,2})?$/', /* 0 to 11 digits and 2 optional decimals */
        'full_cardboard'    => 'required|numeric|regex:/^[\d]{0,11}(\.[\d]{1,2})?$/', /* 0 to 11 digits and 2 optional decimals */
        'start'             =>  'required|datetime',
        'end'               => 'required|datetime'
    ];

    public function __construct() {
        $this->user     = auth()->guard('api')->user();
        $this->rules    = config('bingo.rules');
    }

    public function createMeeting(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        $validator = $this->validator($request->all(), $this->validatorRules, class_basename($this));

        if ($validator->fails()) {
            return $this->validationFail($validator->errors());
        }

        DB::beginTransaction();

        try {
            $this->meeting = new Meeting();
            $this->meeting->name                = $request->name;
            $this->meeting->cardboard_number    = $request->cardboard_number;
            $this->meeting->line_play           = $request->line_play;
            $this->meeting->full_cardboard      = $request->full_cardboard;
            $this->meeting->start               = $request->start;
            $this->meeting->end                 = $request->end;
            $this->meeting->status              = 'creada';
            $this->meeting->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->meeting, 'meeting'));
    }

    public function throwNumber(Request $request) {
        
    }
}