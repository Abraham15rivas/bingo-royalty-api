<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\{
    Auth,
    Validator
};

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $rules =[
            'name'     => 'required',
            'email'    => 'required|string|unique:users',
            'password' => 'required|string|confirmed'
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];

        $input     = $request->only('name', 'email','password');
        $validator = Validator::make($input, $rules);
    
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()]);
        } else {
            $user = new User([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => bcrypt($request->password),
                'role_id'  => 1
            ]);

            $user->save();
            
            return response()->json(['message' => 'Successfully created user!'], 201);
        }
    }

    public function login(Request $request)
    {
        
        $request->validate([
            'email'       => 'required|string|email',
            'password'    => 'required|string',
            'remember_me' => 'boolean',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $user = User::find(Auth::user()->id);
            $msg = 'Usuario logeado con exito';
            $statusCode = 0;
        } else {
            $user = null;
            $msg = 'Las credenciales no coinciden';
            $statusCode = 1;
        }

        if ($user) {
            /*if ($user->role_id === 3) {
                $this->revokeSessionToken($user->id);
            }*/
            $tokenResult = $user->createToken('Token de usuario');
            $token = $tokenResult->token;
            $token->expires_at = Carbon::now()->addWeeks(1);
            $token->save();
        }

        return response()->json([
            'statusCode' => $statusCode,
            'message' => $msg,
            'user' => $user ? ['change_the_first_password' => $user->change_the_first_password] : null,
            'accessToken' => $user ? $tokenResult->accessToken : null,
            'tokenType'   => $user ? 'Bearer' : null,
            'expiresAt'   => $user ? Carbon::parse($tokenResult->token->expires_at)->toDateTimeString() : null
        ]);

    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request) 
    {
        try {
            $user = $request->user();
        } catch (\Throwable $th) {
            $statusCode = 1;
            $msg = 'Hubo un error';
        }

        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'user' => $user ? $user : null
        ]);
    }
}
