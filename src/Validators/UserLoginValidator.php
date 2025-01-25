<?php

namespace App\Validators;

use KrisnaAjieP\PHPValidator\Validator;

/**
 * UserLoginValidator
 */
class UserLoginValidator
{
    /**
     * Validate credentials
     *
     * @param array $data
     * @return Validator
     */
    public static function validate(array $data): Validator
    {
        return Validator::setRules($data, [
            'email' => ['required', 'email', 'max_length:254'],
            'password' => ['required', 'min_length:8', 'max_length:255'],
        ]);
    }
}
