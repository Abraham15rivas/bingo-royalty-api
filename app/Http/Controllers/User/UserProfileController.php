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
}
