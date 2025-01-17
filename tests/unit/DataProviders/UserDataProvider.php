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
}
