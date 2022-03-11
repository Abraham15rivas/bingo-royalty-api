<?php

namespace App\Traits;

trait ResponseTrait {
    public function success($data = [], $result = 'result') {
        return (object) [
            'statusCode' => 0,
            'message' => 'Success',
            "$result" => $data
        ];
    }

    public function serverError($e) {
        return (object) [
            'statusCode' => 1,
            'message'    => 'Error 500 - Internal server error',
            'detail'     => [
                'File'    => $e->getFile(),
                'Message' => $e->getMessage(),
                'Line'    => $e->getLine()
            ]
        ];
    }

    public function notAllowed() {
        return (object) [
            'statusCode' => 2,
            'message' => 'Unauthorized'
        ];
    }

    public function invalidRequest() {
        return (object) [
            'statusCode' => 3,
            'message' => 'The request was not accepted'
        ];
    }

    public function unauthenticated() {
        return (object) [
            'statusCode' => 4,
            'message' => 'unauthenticated'
        ];
    }
}