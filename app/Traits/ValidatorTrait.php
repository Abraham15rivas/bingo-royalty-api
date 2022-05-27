<?php

namespace App\Traits;
use Illuminate\Support\Facades\Validator;

trait ValidatorTrait {
    protected $errors;

    public function validator($data, $validationData, $nameController)
    {
        $customMessages = [
            'required'  => $nameController . 'Validator: :attribute is required.',
            'integer'   => $nameController . 'Validator: :attribute must be an integer.',
            'string'    => $nameController . 'Validator: :attribute must be a string.',
            'boolean'   => $nameController . 'Validator: :attribute must be a boolean.',
            'mimes'     => 'Sólo se permiten archivos de tipo: :values',
            'file.max'  => 'El :attribute supera el máximo permitido de :max KB',
        ];

        return Validator::make($data, $validationData, $customMessages);
    }

    public function customValidator($nameController, $field, $message) {
        $this->errors[$field] = ["$nameController: $field $message"];
        return $this->errors;
    }
}