<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\{
    DB,
    Validator
};
use App\Models\{
    MatrixGroup,
    Matrix
};

class MatrixController extends Controller
{
    use ResponseTrait;

    protected $matrixGroup;
    protected $user;
    protected $rules;

    protected $validationRules = [
        // rules
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
            $matrixGroup = MatrixGroup::select(
                'id',
                'vip',
                'expiration_date as expirationDate',
                DB::raw("extract(day from (expiration_date::timestamp - CURRENT_DATE::timestamp))::int as dayElapsed")
            )
            ->where('expiration_date', '>', Carbon::now())
            ->with('matrices')
            ->get();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($matrixGroup, 'matrices'));
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
            $matrixGroup = MatrixGroup::select(
                'id',
                'vip',
                'expiration_date as expirationDate',
                DB::raw("extract(day from (expiration_date::timestamp - CURRENT_DATE::timestamp))::int as dayElapsed")
            )
            ->where('vip', false)
            ->where('expiration_date', '>', Carbon::now())
            ->orderByDesc('expiration_date')
            ->first();

            if (!isset($matrixGroup->dayElapsed) || $matrixGroup->dayElapsed <= 0) {
                $data   = $this->matrixGenerator($request);
                $saved  = $this->saveMatrix($data);
            }

            if (isset($saved) && $saved === 'done') {
                $result = $saved;
            } else {
                $result = $matrixGroup;
            }
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return response()->json($this->success($result));
    }

    private function cardboardGenerator() {
        $matrix = collect();

        try {
            foreach ($this->rules['letters'] as $key => $item) {
                $matrix[$key] = collect();

                do {
                    $randomInt = random_int($item[0], $item[1]);

                    if ($randomInt >= $item[0] && $randomInt <= $item[1]) {
                        $exists = $matrix[$key]->search($randomInt);

                        if ($exists === false) {
                            $matrix[$key]->push($randomInt);
                        }
                    }
                } while ($this->rules['rowNumber'] > $matrix[$key]->count());
            }
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }

        return $matrix;
    }

    private function matrixGenerator() {
        $currentDate    = Carbon::now();
        $matrix         = collect();

        for ($i = 0; $i < 1000; $i++) {
            $cardboard = $this->cardboardGenerator();

            $matrix->push([
                'id'        => ($i + 1),
                'serial'    =>  '',
                'cardboard' => $cardboard,
                'winer'     => false
            ]);
        }

        $data = collect([
            'matrix'            => $matrix,
            'expirationDate'    => $currentDate->addDays(15)
        ]);

        return $data;
    }

    private function saveMatrix($data) {
        DB::beginTransaction();

        try {
            $matrixGroup = MatrixGroup::create([
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
}
