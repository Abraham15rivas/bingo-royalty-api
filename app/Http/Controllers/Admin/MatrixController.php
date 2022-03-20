<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\{
    MatrixGroup,
    Matrix
};

class MatrixController extends Controller
{
    use ResponseTrait;

    protected $matrixGroup;

    protected $validationRules = [
        'vip'       => 'boolean|required',
        'matrices'  => 'array|nullable'
    ];

    protected $rules = [
        'rowNumber' => 5,
        'letters'   => [
            'B' => [1, 15],
            'I' => [16, 30],
            'N' => [31, 45],
            'G' => [46, 60],
            'O' => [61, 75]
        ]
    ];

    public function index(Request $request) {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $matrixGroup = MatrixGroup::select(
                'id',
                'vip',
                'expiration_date as expirationDate',
                DB::raw("datediff(expiration_date, now()) as dayElapsed")
            )
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
            if ($request->vip === true) {
                // separar o colocar en el metodo: cardboardGeneratorVip();
                // para validar que no exista el mismo carton esta logica de cartones Vip
                // es para los usuarios
                $this->matrixGroup = MatrixGroup::select(
                    'id',
                    'vip',
                    'expiration_date as expirationDate',
                    DB::raw("datediff(expiration_date, now()) as dayElapsed")
                )
                ->where('vip', true)
                ->get();
            } else {
                $matrixGroup = MatrixGroup::select(
                    'id',
                    'vip',
                    'expiration_date as expirationDate',
                    DB::raw("datediff(expiration_date, now()) as dayElapsed")
                )
                ->where('vip', false)
                ->orderByDesc('expiration_date')
                ->first();
            }

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

    private function cardboardGeneratorVip($data) {
        // falta validar los cartones vip here, para saber si estan repetidos
        // Por ahora solo envia el mismo que recibe por indice con:
        // $this->matrixGroup

        return $data;
    }

    private function matrixGenerator($request) {
        $currentDate    = Carbon::now();
        $matrix         = collect();
        $vip            = $request['vip'];

        for ($i = 0; $i < ($vip ? count($request->matrices) : 1000); $i++) {
            $cardboard = $vip ? $this->cardboardGeneratorVip($request->matrices[$i]) : $this->cardboardGenerator();

            if ($cardboard === true) {
                return 'Duplicate cardboard, change combination';
            }

            $matrix->push([
                'id'        => ($i + 1),
                'serial'    =>  '',
                'cardboard' => $cardboard
            ]);
        }

        $data = collect([
            'vip'               => $request['vip'],
            'matrix'            => $matrix,
            'expirationDate'    => $currentDate->addDays($vip ? 1 : 15)
        ]);

        return $data;
    }

    private function saveMatrix($data) {
        DB::beginTransaction();

        try {
            $matrixGroup = MatrixGroup::create([
                'vip'               => $data['vip'],
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
