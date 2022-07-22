<?php

namespace App\Http\Controllers\PlayAssistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\{
    SocketController
};
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};
use App\Models\{
    Meeting,
    User
};
use phpDocumentor\Reflection\Types\This;

class GameController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $rules;
    protected $user;
    protected $meeting;
    protected $meetings;
    protected $cardboard;

    protected $validatorRules = [
        'name'                  => 'required|string',
        'cardboard_number'      => 'required|integer',
        'line_play'             => 'required|numeric|regex:/^[\d]{0,11}(\.[\d]{1,2})?$/', /* 0 to 11 digits and 2 optional decimals */
        'full_cardboard'        => 'required|numeric|regex:/^[\d]{0,11}(\.[\d]{1,2})?$/', /* 0 to 11 digits and 2 optional decimals */
        'start'                 => 'required|date',
        'total_collected'       => 'nullable|integer',
        'accumulated'           => 'nullable|integer',
        'commission'            => 'nullable|integer',
        'reearnings_before_39'  => 'required|integer',
        'reearnings_after_39'   => 'required|integer'
    ];

    public function __construct() {
        $this->user     = auth()->guard('api')->user();
        $this->rules    = config('bingo.rules');
    }

    public function issueNumber($numbers) {
        $socket = new SocketController();
        return $socket->issueNumber($numbers);
    }

    public function index(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $this->meetings = Meeting::select(
                    'id',
                    'name',
                    'start',
                    'cardboard_number',
                    'total_collected',
                    'accumulated',
                    'commission',
                    'reearnings_before_39',
                    'line_play',
                    'full_cardboard',
                    'status',
                    'numbers'
                )
                ->orderBy('start')
                ->get();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->meetings, 'meetings'));
    }
    
    public function show(Request $request, $id) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        DB::beginTransaction();

        try {
            $this->meeting = Meeting::select(
                    'id',
                    'name',
                    'start',
                    'cardboard_number',
                    'total_collected',
                    'accumulated',
                    'commission',
                    'reearnings_before_39',
                    'line_play',
                    'full_cardboard',
                    'status',
                    'numbers'
                )
                ->withCount('users')
                ->find($id);

            if (!$this->meeting) {
                $this->meeting = 'No existe la sala';
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->meeting, 'meeting'));
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
            $this->meeting->name                    = $request->name;
            $this->meeting->cardboard_number        = $request->cardboard_number;
            $this->meeting->line_play               = $request->line_play;
            $this->meeting->full_cardboard          = $request->full_cardboard;
            $this->meeting->start                   = $request->start;
            $this->meeting->status                  = 'creada';
            $this->meeting->total_collected         = $request->total_collected;
            $this->meeting->accumulated             = $request->accumulated;
            $this->meeting->commission              = $request->commission;
            $this->meeting->reearnings_before_39    = $request->reearnings_before_39;
            $this->meeting->reearnings_after_39     = $request->reearnings_after_39;
            $this->meeting->referred                = $request->referred;
            $this->meeting->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->meeting, 'meeting'));
    }

    public function initMeeting(Request $request, $id) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        DB::beginTransaction();

        try {
            $this->meeting = Meeting::where('id', $id)
                ->where('status', 'creada')
                ->first();

                if ($this->meeting) {
                    $this->meeting->update(['status' => 'en progreso']); 
                } else {
                    $this->meeting = 'No hay salas creadas';
                }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->meeting, 'meeting'));
    }

    public function throwNumber(Request $request, $id) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        $validatorRules['lyrics'] = 'required|string|max:1';
        $validatorRules['number'] = 'required|integer:max:2';

        $validator = $this->validator($request->all(), $validatorRules, class_basename($this));

        if ($validator->fails()) {
            return $this->validationFail($validator->errors());
        }

        $response = (object) [];

        DB::beginTransaction();

        try {
            $lyrics = strtoupper($request->lyrics);
            $number = $request->number;

            if ($number < $this->rules['minNumber'] || $number > $this->rules['maxNumber']) {
                $errors = $this->customValidator(class_basename($this), 'Numero fuera de rango', 'Permitidos, desde: ' . $this->rules['minNumber'] . ' hasta: ' . $this->rules['maxNumber']);
                return response()->json($this->validationFail($errors));
            } else {
                if (isset($this->rules['letters'][$lyrics])) {
                    $minLyrics = $this->rules['letters'][$lyrics][0];
                    $maxLyrics = $this->rules['letters'][$lyrics][1];

                    if ($number < $minLyrics || $number > $maxLyrics) {
                        $errors = $this->customValidator(class_basename($this), 'Numero fuera de rango', 'Permitidos para ' . $lyrics . ' , desde: ' . $minLyrics . ' hasta: ' . $maxLyrics);
                        return response()->json($this->validationFail($errors));
                    }
                } else {
                    $errors = $this->customValidator(class_basename($this), 'Letra fuera de rango', 'Letra no esta en los rangos debe ser alguna de las siguientes: BINGO');
                    return response()->json($this->validationFail($errors));
                }

                $this->meeting = Meeting::select(
                        'id',
                        'numbers'
                    )
                    ->find($id);

                if ($this->meeting) {
                    $existingNumbers = json_decode($this->meeting->numbers);

                    if ($existingNumbers) {
                        foreach ($existingNumbers as $item) {
                            if ($item->lyrics === $lyrics) {
                                if ($item->number === $number) {
                                    $errors = $this->customValidator(class_basename($this),'Número repetido', 'por favor reintentar');
                                    break;
                                }
                            }
                        }
                    }

                    if(isset($errors)) {
                        return response()->json($this->validationFail($errors));
                    }

                    $receivedNumber = collect([
                        'lyrics' => $lyrics,
                        'number' => $number
                    ]);

                    if ($existingNumbers) {
                        array_push($existingNumbers, $receivedNumber);
                        $numbers = collect($existingNumbers)->toJson();
                    }

                    $this->meeting->numbers = isset($numbers) ? $numbers : [$receivedNumber];

                    if ($this->meeting->save()) {
                        $this->issueNumber($this->meeting->numbers);
                        $response = 'Número ingresado correctamente';
                    }
                } else {
                    $errors = $this->customValidator(class_basename($this),'No hay salas creadas', 'por favor reintentar');
                    return response()->json($this->validationFail($errors));
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($response));
    }

    public function connectMeeting(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        DB::beginTransaction();

        try {
            $this->meeting = Meeting::where('status', 'en progreso')
                ->first();

            if ($this->meeting) {
                $limit = $this->meeting->cardboard_number ?? 1;

                $user = User::with(['userCardboards' => function($query) use ($limit) {
                        $query->select(
                            'id',
                            'status',
                            'serial',
                            'user_id',
                            'cardboard'
                        )
                        ->orderByDesc('created_at')
                        ->limit($limit);
                    }])
                    ->where('status', 'conectado')
                    ->find($this->user->id);

                if (!empty($user)) {
                    $cardboardsAvailables = $user
                        ->userCardboards
                        ->where('status', 'available')
                        ->values();

                    if (!empty($cardboardsAvailables)) {
                        $user->update([
                            'status' => 'jugando'
                        ]);

                        foreach ($cardboardsAvailables as $cardboard) {
                            $cardboard->status = 'inGame';
                            $cardboard->save();
                        }

                        $this->meeting->users()->attach($this->user->id);
                    } else {
                        $errors = $this->customValidator(class_basename($this), 'El usuario no poseé cartones disponibles para ingresar a la sala', 'compra para poder participar en esta ronda');
                        return response()->json($this->validationFail($errors));
                    }
                } else {
                    $errors = $this->customValidator(class_basename($this), 'Estatus', 'El usuario esta ' . $this->user->status . ' actualmente');
                    return response()->json($this->validationFail($errors));
                }
            } else {
                $errors = $this->customValidator(class_basename($this), 'Disponibilidad', 'No hay salas disponibles');
                return response()->json($this->validationFail($errors));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success([]));
    }

    public function nextPlay(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $this->meeting = Meeting::select(
                    'id',
                    'name',
                    'start',
                    'cardboard_number',
                    'total_collected',
                    'accumulated',
                    'commission',
                    'reearnings_before_39',
                    'line_play',
                    'full_cardboard',
                    'status',
                    'numbers'
                )
                ->where('status', '!=', 'finalizada')
                ->where('status', '!=', 'en progreso')
                ->orderBy('start')
                ->first();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->meeting, 'meeting'));
    }

    public function cardboardInPlay(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $user = User::select(
                    'id',
                    'email',
                    'status'
                )
                ->with(['userCardboards' => function($query) {
                $query->select(
                    'id',
                    'status',
                    'serial',
                    'user_id',
                    'cardboard'
                )
                ->where('status', 'inGame')
                ->orderByDesc('created_at')
                ->get();
            }])
            ->find($this->user->id);

            if (isset($user->userCardboards)) {
                $this->cardboard = $user->userCardboards;
            }
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->cardboard, 'cardboard'));
    }
}