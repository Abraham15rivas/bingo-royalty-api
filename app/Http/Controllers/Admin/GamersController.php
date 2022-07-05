<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};
use App\Models\{User, Role, Profile};
use Illuminate\Support\Facades\{
    Auth,
    Validator
};

class GamersController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    protected $user;

    public function __construct() {
        $this->user  = auth()->guard('api')->user();
    }

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $gamers = User::where('role_id', '=', 3)
                ->get();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }
        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'gamers' => isset($gamers) ? $gamers : $gamers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $rules =[
            'name'          => 'required',
            'email'         => 'required|string|unique:users',
            'password'      => 'min:6',
        ];

        $customMessages = [
            'required'  => 'El :attribute es requerido.',
            'min'   => 'El :attribute debe ser mayor a 6 caracteres.',
            'unique'    => 'El correo electrónico ya esta siendo utilizado.'
        ];

        $input     = $request->only('name', 'email','password', 'rol');
        $validator = Validator::make($input, $rules, $customMessages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'error' => $validator->messages()
            ]);
        } else {
            try {
                $user = new User([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => bcrypt($request->password),
                    'referral_code' => 'Admin',
                    'role_id'  => $request->rol
                ]);
                $user->save();

                $profile = Profile::create([
                    'user_id' => $user->id,
                    'name'    => $request->name,
                    'profile_image' => '/storage/profile/usuario.png'
                ]);

                $profile->save();
            } catch (\Exception $e) {
                return response()->json($this->serverError($e));
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Usuario creado correctamente!'
            ], 201);
        }
    }

    public function indexRoles(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $roles = Role::select([
                'id',
                'name'
            ])->get();
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }
        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'roles' => isset($roles) ? $roles : $roles
        ]);
    }

    public function show(Request $request, $id)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }

        try {
            $gamer = User::select(
                'id',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'referral_code',
                'vip'
            )
            ->with('wallet', 'profile')
            ->find($id);
        } catch (\Exception $e) {
            return response()->json($this->serverError($e));
        }
        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'gamer' => isset($gamer) ? $gamer : $gamer
        ]);
    }

    public function deactiveGamer(Request $request, $id)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }        

        try {
            $gamer = User::where('id', $id)
                ->first();

            $gamer->is_active = !$gamer->is_active;
            $gamer->save();
            
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


