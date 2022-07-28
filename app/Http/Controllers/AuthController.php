<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    User, 
    Profile,
    Wallet
};
use Carbon\Carbon;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\{
    Auth,
    Validator
};

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        
        $rules =[
            'name'          => 'required',
            'email'         => 'required|string|unique:users',
            'password'      => 'min:6|confirmed',
            'referral_code' => 'string',
        ];

        $customMessages = [
            'required'  => 'El :attribute es requerido.',
            'confirmed' => 'Las contraseñas no coinciden.',
            'min'   => 'El :attribute debe ser mayor a 6 caracteres.',
            'unique'    => 'El correo electrónico ya esta siendo utilizado.'
        ];

        $input     = $request->only('name', 'email','password', 'password_confirmation');
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
                    'referral_code' => User::getUniqueReferralCode(),
                    'referred_by' => $this->getReferredBy($request->referral_code),
                    'role_id'  => 3 
                ]);
                
                $user->save();

                $profile = Profile::create([
                    'user_id' => $user->id,
                    'name'    => $request->name,
                    'profile_image' => '/storage/profile/usuario.png'
                ]);

                $profile->save();
               
                if ($user->role_id === 3) {
                   
                    $wallet = Wallet::create([
                        'user_id' => $user->id,
                        'name'    => 'Wallet de ' . $request->name,
                        'balance' => 0.00,
                    ]);
                    $wallet->save();
                }
            } catch (\Exception $e) {
                return response()->json($this->serverError($e));
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Usuario creado correctamente!'
            ], 201);
        }
    }

    private function getReferredBy($referralCode)
    {
        if ($referralCode)
            return User::where('referral_code', $referralCode)->value('id');

        return null;
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

            if (!$user->is_active) {

                $user = null;
                $msg = 'Su cuenta se encuentra desactivada - bingo@support.com';
                $statusCode = 1;

                return response()->json([
                    'statusCode' => $statusCode,
                    'message' => $msg,
                    'user' => $user 
                ]);
            }

            $msg = 'Usuario logeado con exito';
            $statusCode = 0;
        } else {
            $user = null;
            $msg = 'Las credenciales no coinciden';
            $statusCode = 1;
        }

        if ($user) {
            $tokenResult = $user->createToken('Token de usuario');
            $token = $tokenResult->token;
            $token->expires_at = Carbon::now()->addWeeks(1);
            $token->save();
        }

        $user->profile; 
        $user->wallet; 

        return response()->json([
            'statusCode' => $statusCode,
            'message' => $msg,
            'user' => $user ? $user : null,
            'accessToken' => $user ? $tokenResult->accessToken : null,
            'tokenType'   => $user ? 'Bearer' : null,
            'expiresAt'   => $user ? Carbon::parse($tokenResult->token->expires_at)->toDateTimeString() : null
        ]);

    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        $request->user()->status = 'desconectado';
        $request->user()->save();

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
            'imgProfile' => $user->profile->profile_image ? $user->profile->profile_image : null
        ]);
    }

    /**
     * Send password reset link. 
     */
    public function sendPasswordResetLink(Request $request)
    {
        return $this->sendResetLinkEmail($request);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json([
            'message' => 'Se ha enviado un correo electrónico para restablecer la contraseña.',
            'data' => $response
        ]);
    }
    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json(['message' => 'No se ha podido enviar el correo electrónico a esta dirección']);
    }

    /**
     * Handle reset password 
     */
    public function callResetPassword(Request $request)
    {
        return $this->reset($request);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->save();
        event(new PasswordReset($user));
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        return response()->json(['message' => 'Contrasena restablecida correctamente.']);
    }
    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return response()->json(['message' => 'Error, token invalido']);
    }
}
