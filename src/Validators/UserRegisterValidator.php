<?php

namespace App\Validators;

use KrisnaAjieP\PHPValidator\Validator;

/**
 * UserRegisterValidator
 */
class UserRegisterValidator
{
    /**
     * Validate user register data
     *
     * @param array $data
     * @return Validator
     */
    public static function validate(array $data): Validator
    {
        return Validator::setRules($data, [
            'name' => ['required', 'alpha', 'min_length:2', 'max_length:50'],
            'email' => ['required', 'email', 'max_length:254'],
            'password' => ['required', 'min_length:8', 'max_length:255'],
            'password_confirmation' => ['required', 'match:password'],
        ]);
    }
}
