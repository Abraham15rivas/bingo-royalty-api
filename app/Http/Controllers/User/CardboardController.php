<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\{
    DB,
    Validator
};
use App\Traits\{
    ResponseTrait,
    SeriateTrait
};
use App\Models\{
    MatrixGroup,
    Matrix,
    UserCardboard
};

class CardboardController extends Controller
{
    use ResponseTrait, SeriateTrait;

    protected $serial;
    protected $user;
    protected $rules;
    protected $cardboard;

    protected $validationRules = [
        'matrices' => 'array|required'
    ];

    public function __construct() {
        $this->user     = auth()->guard('api')->user();
        $this->rules    = config('bingo.rules');
    }

    public function index(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $this->cardboard = UserCardboard::select(
                'serial',
                'status',
            )
            ->where('user_id', $this->user->id)
            ->orderByDesc('id')
            ->get();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($this->cardboard, 'cardboards'));
    }

    public function store(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        $validator = Validator::make($request->all(), $this->validationRules);

        if ($validator->fails()) {
            return response()->json($this->validationFail($validator->errors()));
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
            $cardboard = $this->cardboardGeneratorVip($request->matrices[$i]);

            if ($cardboard === true) {
                return 'Duplicate cardboard, change combination';
            }

            $this->serial = $this->generateSeries();

            $assignCardboard = $this->assignCardboard();

            if ($assignCardboard === 'done') {
                $matrix->push([
                    'id'            => ($i + 1),
                    'serial'        => $this->serial,
                    'cardboard'     => $cardboard,
                    'numberOfPlays' => 20
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
                'user_id'   => $this->user->id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->serverError($e));
        }

        return 'done';
    }
}