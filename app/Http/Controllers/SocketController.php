<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Traits\{
    ResponseTrait,
    ValidatorTrait
};

class SocketController extends Controller
{
    use ResponseTrait, ValidatorTrait;

    public function issueNumber($numbers) {
        try {
            $url = $this->nodeUrl . '/issue-number';
            $response = Http::withHeaders([
                    'content-type' => 'application/json'
                ])->post($url, [
                    'numbers' => $numbers
                ])->json();
        } catch (\Exception $e) {
            $this->serverError($e);
            return false;
        }

        return $this->success($response);
    }
}
