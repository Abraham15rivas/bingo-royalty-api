<?php

namespace App\Traits;

trait CardboardTrait {
    protected $cardboardObject;

    public function getListCardboard($jsonCardboards) {
        try {
            $this->cardboardObject = collect(json_decode($jsonCardboards));                
        } catch (\Exception $e) {
            return $this->serverError($e);
        }

        return $this->success($this->cardboardObject, 'cardboardObject');
    }
}