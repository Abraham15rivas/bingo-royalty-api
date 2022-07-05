<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, Response};

use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};

class ReferralController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    public function link(Request $request, $referralCode)
    {   
        if (!$request->ajax()) {
            return response()->json($this->invalidRequest());
        }  

        if (!$request->hasCookie('referral')) {
            $response = new Response('create cookie');
            $response->withCookie(cookie('referral', $referralCode, 60 * 24 * 7));
            return $response;
        }
        return response()->json('ya tiene cookie');
    }
}
