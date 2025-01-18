<?php

namespace Test\Unit\DataProviders;

final class UserDataProvider
{
    public static function creationProvider(): array
    {
        return [
            'created data' => [
                [
                    'name' => 'John Doe',
                    'email' => 'john@doe.com',
                    'password' => 'password'
                ]
            ]
        ];
    }

    public static function validRegistrationProvider(): array
    {
        return [
            'valid registration data' => [
                [
                    'name' => 'John Doe',
                    'email' => 'john@doe.com',
                    'password' => 'password',
                    'password_confirmation' => 'password'
                ],
            ],
        ];
    }

    public static function invalidRegistrationProvider(): array
    {
        return [
            'invalid registration data' => [
                [
                    'name' => '',
                    'email' => 'johndoe.com',
                    'password' => 'password',
                    'password_confirmation' => 'drowsapp'
                ],
            ],
        ];
    }

    public static function validAuthenticationProvider(): array
    {
        return [
            'valid credentials' => [
                [
                    'email' => 'john@doe.com',
                    'password' => 'password',
                ],
            ],
        ];
    }

    public static function invalidAuthenticationProvider(): array
    {
        return [
            'wrong password' => [
                [
                    'email' => 'john@doe.com',
                    'password' => 'drowsapp',
                ],
            ],
        ];
    }

    public static function invalidLoginProvider(): array
    {
        return [
            'invalid credentials' => [
                [
                    'email' => 'johndoe.com',
                ],
            ],
        ];
    }
}
