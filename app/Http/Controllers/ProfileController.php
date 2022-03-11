<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Profile};

class ProfileController extends Controller
{
   /* public function __construct()
    {
        dd(User::all());
    }*/

    public function show(Request $request)
    {
        return response()->json($request->user());
    }
}
