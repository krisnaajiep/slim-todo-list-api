<?php

namespace Test\Unit\DataProviders;

final class TodoDataProvider
{
    public static function creationProvider(): array
    {
        return [
            'valid todo data' => [
                [
                    'user_id' => rand(1, 100),
                    'title' => 'Buy Groceries',
                    'description' => 'Buy milk, eggs, and bread'
                ]
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
