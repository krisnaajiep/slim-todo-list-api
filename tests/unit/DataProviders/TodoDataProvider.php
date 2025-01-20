<?php

namespace Test\Unit\DataProviders;

final class TodoDataProvider
{
    public static function creationProvider(): array
    {
        return [
            'valid todo data' => [
                [
                    'user_id' => 0,
                    'title' => 'Buy Groceries',
                    'description' => 'Buy milk, eggs, and bread'
                ]
            ]
        ];
    }

    public static function modificationProvider(): array
    {
        return [
            'valid todo data' => [
                [
                    'user_id' => 0,
                    'title' => 'Buy Groceries',
                    'description' => 'Buy milk, eggs, bread, and cheese'
                ],
                0
            ]
        ];
    }

    public static function invalidModificationProvider(): array
    {
        return [
            'invalid id throws 404' => [
                [
                    'user_id' => 0,
                    'title' => 'Buy Groceries',
                    'description' => 'Buy milk, eggs, bread, and cheese'
                ],
                2
            ],
            'unauthorized user id throws 403' => [
                [
                    'user_id' => 0,
                    'title' => 'Buy Groceries',
                    'description' => 'Buy milk, eggs, bread, and cheese'
                ],
                1
            ],
            'invalid todo data' => [
                [
                    'user_id' => 0,
                    'title' => 'Gr',
                    'description' => ''
                ],
                0
            ]
        ];
    }

    public static function invalidCreationProvider(): array
    {
        return [
            'invalid todo data' => [
                [
                    'user_id' => rand(1, 100),
                    'title' => 'Gr',
                    'description' => ''
                ]
            ]
        ];
    }
}
