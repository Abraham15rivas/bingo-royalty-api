<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Profile;

class UserProfileController extends Controller
{
    public function show(Request $request)
    {
        try {
            $id = $request->user()->id;
            $profile = Profile::where('user_id', $id)->get();        
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
        $validatedData = $request->validate([
            'name' => 'required',
            'last_name' => 'required',
            'nick_name' => 'required',
            'country' => 'required',
            'profile_image' => 'required|mimes:jpg,jpeg,png|max:2048'
        ]);

       

        try {
            $profile = Profile::where('user_id', $request->user()->id)->get();
            $profile = $profile[0];
            
            $profile->name = $request->name;
            $profile->last_name = $request->last_name;
            $profile->nick_name = $request->nick_name;
            $profile->country = $request->country;

            if($request->file('profile_image')) {
                $file_name = time().'-'.$profile->name.'-'.$request->file('profile_image')->getClientOriginalName();
                $file_path = $request->file('profile_image')->storeAs('uploads', $file_name, 'public');
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
}
