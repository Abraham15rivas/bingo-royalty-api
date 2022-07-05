<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{Profile, User};
use Illuminate\Support\Facades\Validator;

use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};

class UserProfileController extends Controller
{    
    use ResponseTrait, ValidatorTrait;

    public function show(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }  
        
        try {
            $profile = Profile::where('user_id', $request->user()->id)->first();        
        } catch (\Throwable $th) {
            $this->reportError($th);
            $statusCode = 1;
            $msg = 'Hubo un error';
        }

        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'profile' => $profile ? $profile : null
        ]);
    }

    public function store(Request $request)
    { 
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }  

        $validatedData = $request->validate([
            'name' => 'required',
            'last_name' => 'required',
            'nick_name' => 'required',
            'country' => 'required',
            'profile_image' => 'mimes:jpg,jpeg,png|max:2048|nullable'
        ]);

        $customMessages = [
            'required'  => 'El :attribute es requerido.',
            'mimes'   => 'El tipo de imagen debe ser jpg, jpeg o png.',
        ];

        $validator = Validator::make($validatedData, $customMessages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'error' => $validator->messages()
            ]);
        } 

        try {
            $profile = Profile::where('user_id', $request->user()->id)->first();
            
            $profile->name = $request->name;
            $profile->last_name = $request->last_name;
            $profile->nick_name = $request->nick_name;
            $profile->country = $request->country;

            if($request->file('profile_image')) {
                $file_name = $profile->name.'-'.$request->file('profile_image')->getClientOriginalName();
                $file_path = $request->file('profile_image')->storeAs('profile', $file_name, 'public');
                $profile->profile_image = '/storage/' . $file_path;
            }

            $profile->save();

        } catch (\Throwable $th) {
            $statusCode = 1;
            $msg = 'Hubo un error';
        }

        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'user' => isset($profile) ? $profile : (object) []
        ]);
    }

    public function notifications (Request $request)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }        

        try {
            $user = Profile::where('user_id', $request->user()->id)
                ->first();
            $user->notifications = !$user->notifications;
            $user->save();
            
        } catch (\Throwable $th) {
            $statusCode = 1;
            $msg = 'Hubo un error';
        }

        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'notification' => $user->notifications
        ]);
    }

    public function disableAccount (Request $request)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }        

        try {
            $user = User::where('id', $request->user()->id)
                ->first();
            $user->is_active = false;
            $user->save();
            
        } catch (\Throwable $th) {
            $statusCode = 1;
            $msg = 'Hubo un error';
        }

        return response()->json([
            'statusCode' => isset($statusCode) ? $statusCode : 0,
            'message' => isset($msg) ? $msg : 'Success',
            'user' => $user->is_active
        ]);
    }

    public function userVip(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }        

        try {
            $user = User::where('id', $request->user()->id)
                ->first();
            $user->vip = true;
            $user->save();
            
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
