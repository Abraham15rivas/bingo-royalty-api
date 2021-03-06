<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\{
    ResponseTrait,
    SeriateTrait,
    ValidatorTrait,
    CardboardTrait
};
use App\Models\{
    MatrixGroup,
    Matrix,
    UserCardboard
};

class CardboardController extends Controller
{
    use ResponseTrait, SeriateTrait, ValidatorTrait, CardboardTrait;

    protected $serial;
    protected $user;
    protected $rules;
    protected $cardboard;
    protected $cardboards;
    protected $matrixGroup;

    protected $validatorRules = [];

    public function __construct() {
        $this->user     = auth()->guard('api')->user();
        $this->rules    = config('bingo.rules');
    }

    public function index(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $this->cardboards = UserCardboard::select(
                'serial',
                'status',
                'cardboard'
            )
            ->where('user_id', $this->user->id)
            ->orderByDesc('id')
            ->get();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->cardboards, 'cardboards'));
    }

    public function store(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        $this->validatorRules = [
            'matrices' => 'array|required'
        ];

        $validator = $this->validator($request->all(), $this->validatorRules, class_basename($this));

        if ($validator->fails()) {
            return $this->validationFail($validator->errors());
        }

        try {
            $data = $this->matrixGenerator($request);

            if (isset($data->matrix)) {
                $result = $data;
            } else {
                $result = $this->saveMatrix($data);
            }
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($result));
    }

    private function cardboardGeneratorVip($data) {
        // falta validar los cartones vip here, para saber si estan repetidos
        return $data;
    }

    private function matrixGenerator($request) {
        $currentDate    = Carbon::now();
        $matrix         = collect();

        for ($i = 0; $i < count($request->matrices); $i++) {
            $this->cardboard = $this->cardboardGeneratorVip($request->matrices[$i]);

            if ($this->cardboard === true) {
                return 'Duplicate cardboard, change combination';
            }

            $this->serial = $this->generateSeries();

            $assignCardboard = $this->assignCardboard();

            if ($assignCardboard === 'done') {
                $matrix->push([
                    'id'            => ($i + 1),
                    'serial'        => $this->serial,
                    'cardboard'     => $this->cardboard,
                    'numberOfPlays' => 20,
                    'winer'         => false
                ]);
            } else {
                return $assignCardboard;
            }
        }

        $data = collect([
            'matrix'            => $matrix,
            'expirationDate'    => $currentDate->addDay()
        ]);

        return $data;
    }

    private function saveMatrix($data) {
        DB::beginTransaction();

        try {
            $matrixGroup = MatrixGroup::create([
                'vip'               => true,
                'expiration_date'   => $data['expirationDate']
            ]);

            if ($matrixGroup) {
                Matrix::create([
                    'matrix_group_id'   => $matrixGroup->id,
                    'cardboards'        => $data['matrix']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return 'done';
    }

    private function assignCardboard() {
        DB::beginTransaction();

        try {
            UserCardboard::create([
                'status'    => 'inGame',
                'serial'    => $this->serial,
                'user_id'   => $this->user->id,
                'cardboard' => json_encode($this->cardboard)
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return 'done';
    }

    public function listCardboard(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $this->matrixGroup = MatrixGroup::select(
                'matrix_groups.id',
                'matrix_groups.vip',
                'matrix_groups.expiration_date as expirationDate',
                DB::raw("extract(day from (matrix_groups.expiration_date::timestamp - CURRENT_DATE::timestamp))::int as dayElapsed"),
                'matrices.cardboards'
            )
            ->join('matrices', 'matrices.matrix_group_id', 'matrix_groups.id')
            ->where('matrix_groups.vip', false)
            ->where('matrix_groups.expiration_date', '>', Carbon::now())
            ->first();

            $listCardboards = [];

            if (isset($this->matrixGroup->cardboards)) {
                $cardboardResponse = $this->getListCardboard($this->matrixGroup->cardboards);

                if ($cardboardResponse->statusCode === 0) {
                    $listCardboards = $cardboardResponse
                        ->cardboardObject
                        ->where('serial', "")
                        ->values();
                }
            }
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($listCardboards, 'listCardboards'));
    }

    public function buyCardboard(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        $this->validatorRules = [
            'id' => 'integer|required'
        ];

        $validator = $this->validator($request->all(), $this->validatorRules, class_basename($this));

        if ($validator->fails()) {
            return $this->validationFail($validator->errors());
        }

        if ($this->user->wallet->balance <= 0) {
            $errors = $this->customValidator(class_basename($this), 'balanceToSend', 'fondos insuficientes.');
            return response()->json($this->validationFail($errors));
        }

        try {
            $this->matrixGroup = MatrixGroup::select(
                'matrix_groups.id',
                'matrix_groups.vip',
                'matrix_groups.expiration_date as expirationDate',
                DB::raw("extract(day from (matrix_groups.expiration_date::timestamp - CURRENT_DATE::timestamp))::int as dayElapsed"),
                'matrices.cardboards',
                'matrices.id as matrix_id'
            )
            ->join('matrices', 'matrices.matrix_group_id', 'matrix_groups.id')
            ->where('matrix_groups.vip', false)
            ->where('matrix_groups.expiration_date', '>', Carbon::now())
            ->first();

            $listCardboards = [];

            if (isset($this->matrixGroup->cardboards)) {
                $cardboardResponse = $this->getListCardboard($this->matrixGroup->cardboards);

                if ($cardboardResponse->statusCode === 0) {
                    $listCardboards = $cardboardResponse
                        ->cardboardObject;

                    $this->serial = $this->generateSeries();

                    foreach ($listCardboards as $matrixCardboard) {
                        if ($matrixCardboard->id === $request->id) {
                            $matrixCardboard->serial = $this->serial;
                            $this->cardboard = $matrixCardboard->cardboard;
                            break;
                        }
                    }

                    if (isset($this->matrixGroup->matrix_id)) {
                        $matrix = Matrix::select(
                                'id',
                                'cardboards'
                            )
                            ->find($this->matrixGroup->matrix_id);

                        if (!empty($matrix)) {
                            $matrix->update([
                                'cardboards' => $listCardboards
                            ]);

                            $this->assignCardboard();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success([]));
    }
}