<?php

namespace Test\Unit\DataProviders;

final class UserDataProvider
{
    public static function creationProvider(): array
    {
        return [
            'valid user data' => [
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
                ],
                'invalid registration data'
            ],
            'duplicate email' => [
                [
                    'name' => 'John Doe',
                    'email' => 'john@doe.com',
                    'password' => 'password',
                ],
                'duplicate email'
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
            'incorrent password' => [
                [
                    'email' => 'john@doe.com',
                    'password' => 'drowsapp',
                ],
                'incorrect password'
            ],
            'invalid credentials' => [
                [
                    'email' => 'johndoe.com',
                ],
                'invalid credentials'
            ],
        ];
    }

    public static function incorrectPasswordAuthenticationProvider(): array
    {
        return [
            'incorrect password' => [
                [
                    'email' => 'john@doe.com',
                    'password' => 'drowsapp'
                ],
            ],
        ];
    }
}
