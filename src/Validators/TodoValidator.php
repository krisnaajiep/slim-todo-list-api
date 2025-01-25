<?php

namespace App\Validators;

use KrisnaAjieP\PHPValidator\Validator;

/**
 * TodoValidator
 */
class TodoValidator
{
    /**
     * Validate todo data
     *
     * @param array $data
     * @return Validator
     */
    public static function validate(array $data): Validator
    {
        return Validator::setRules($data, [
            'title' => ['required', 'min_length:3', 'max_length:100'],
            'description' => ['required', 'max_length:1000'],
        ]);
    }
}
