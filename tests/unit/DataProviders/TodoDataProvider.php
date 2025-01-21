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

    public static function deletionProvider(): array
    {
        return [
            'valid todo id' => [0, 0]
        ];
    }

    public static function invalidDeletionProvider(): array
    {
        return [
            'invalid id throws 404' => [2, 0],
            'unauthorized user id throws 403' => [0, 1]
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

    public static function retrievalProvider(): array
    {
        return [
            'todo items' =>  [
                [
                    [
                        'id' => 1,
                        'user_id' => 0,
                        'title' => 'Buy groceries',
                        'status' => 'todo',
                        'description' => 'Buy milk, eggs, bread',
                        'created_at' => '2025-01-20T19:16:35.000z',
                        'updated_at' => '2025-01-20T22:16:22.000z'
                    ],
                    [
                        'id' => 2,
                        'user_id' => 0,
                        'title' => 'Pay bills',
                        'description' => 'Pay electricity and water bills',
                        'status' => 'todo',
                        'created_at' => '2025-01-20T19:16:35.000z',
                        'updated_at' => '2025-01-20T22:16:22.000z'
                    ]
                ],
                0
            ]
        ];
    }
}
