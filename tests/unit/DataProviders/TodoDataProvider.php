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
                    'description' => 'Buy milk, eggs, bread, and cheese',
                    'status' => 'in progress'
                ],
                0
            ],
            'null todo status' => [
                [
                    'user_id' => 0,
                    'title' => 'Buy Groceries',
                    'description' => 'Buy milk, eggs, bread, and cheese',
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
                    'description' => '',
                    'status' => 'single'
                ],
                0
            ]
        ];
    }

    public static function throwExceptionModificationProvider(): array
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
            'pagination' =>  [
                [
                    [
                        "id" => 0,
                        "title" => "Buy groceries",
                        "description" => "Buy milk, eggs, bread",
                        "status" => "done",
                        "created_at" => "2025-01-20T19:16:35.000Z",
                        "updated_at" => "2025-01-20T19:16:35.000Z"
                    ],
                    [
                        "id" => 1,
                        "title" => "Pay bills",
                        "description" => "Pay electricity and water bills",
                        "status" => "done",
                        "created_at" => "2025-01-19T18:10:22.000Z",
                        "updated_at" => "2025-01-20T19:12:35.000Z"
                    ],
                    [
                        "id" => 2,
                        "title" => "Clean the house",
                        "description" => "Vacuum living room and clean kitchen",
                        "status" => "done",
                        "created_at" => "2025-01-18T17:00:10.000Z",
                        "updated_at" => "2025-01-19T16:10:45.000Z"
                    ],
                    [
                        "id" => 3,
                        "title" => "Workout",
                        "description" => "Go for a 30-minute run",
                        "status" => "done",
                        "created_at" => "2025-01-20T08:20:15.000Z",
                        "updated_at" => "2025-01-20T09:25:45.000Z"
                    ],
                    [
                        "id" => 4,
                        "title" => "Call mom",
                        "description" => "Check in and see how she's doing",
                        "status" => "done",
                        "created_at" => "2025-01-19T14:00:00.000Z",
                        "updated_at" => "2025-01-19T14:30:00.000Z"
                    ],
                    [
                        "id" => 5,
                        "title" => "Finish project report",
                        "description" => "Complete and submit the report by evening",
                        "status" => "done",
                        "created_at" => "2025-01-18T20:10:30.000Z",
                        "updated_at" => "2025-01-19T21:15:30.000Z"
                    ],
                    [
                        "id" => 6,
                        "title" => "Book dentist appointment",
                        "description" => "Schedule a checkup for next week",
                        "status" => "done",
                        "created_at" => "2025-01-17T11:10:20.000Z",
                        "updated_at" => "2025-01-18T12:12:22.000Z"
                    ],
                    [
                        "id" => 7,
                        "title" => "Plan weekend trip",
                        "description" => "Decide on a destination and book tickets",
                        "status" => "in progress",
                        "created_at" => "2025-01-15T15:45:00.000Z",
                        "updated_at" => "2025-01-16T10:30:45.000Z"
                    ],
                    [
                        "id" => 8,
                        "title" => "Fix leaky faucet",
                        "description" => "Replace washer in the kitchen sink",
                        "status" => "in progress",
                        "created_at" => "2025-01-20T07:05:15.000Z",
                        "updated_at" => "2025-01-20T07:10:25.000Z"
                    ],
                    [
                        "id" => 9,
                        "title" => "Read a book",
                        "description" => "Finish reading 'Atomic Habits'",
                        "status" => "in progress",
                        "created_at" => "2025-01-14T20:00:00.000Z",
                        "updated_at" => "2025-01-18T15:00:00.000Z"
                    ],
                    [
                        "id" => 10,
                        "title" => "Prepare dinner",
                        "description" => "Cook spaghetti and garlic bread",
                        "status" => "in progress",
                        "created_at" => "2025-01-20T17:00:00.000Z",
                        "updated_at" => "2025-01-20T17:30:00.000Z"
                    ],
                    [
                        "id" => 11,
                        "title" => "Attend meeting",
                        "description" => "Project status update at 3 PM",
                        "status" => "in progress",
                        "created_at" => "2025-01-19T14:30:00.000Z",
                        "updated_at" => "2025-01-19T15:00:00.000Z"
                    ],
                    [
                        "id" => 12,
                        "title" => "Update resume",
                        "description" => "Add recent project experience",
                        "status" => "in progress",
                        "created_at" => "2025-01-15T10:20:00.000Z",
                        "updated_at" => "2025-01-17T12:20:00.000Z"
                    ],
                    [
                        "id" => 13,
                        "title" => "Organize desk",
                        "description" => "Sort papers and tidy workspace",
                        "status" => "in progress",
                        "created_at" => "2025-01-20T09:10:00.000Z",
                        "updated_at" => "2025-01-20T10:15:00.000Z"
                    ],
                    [
                        "id" => 14,
                        "title" => "Check car tires",
                        "description" => "Inspect and inflate tires as needed",
                        "status" => "in progress",
                        "created_at" => "2025-01-19T11:00:00.000Z",
                        "updated_at" => "2025-01-19T11:30:00.000Z"
                    ],
                    [
                        "id" => 15,
                        "title" => "Learn new recipe",
                        "description" => "Try making homemade sushi",
                        "status" => "todo",
                        "created_at" => "2025-01-18T16:00:00.000Z",
                        "updated_at" => "2025-01-19T18:30:00.000Z"
                    ],
                    [
                        "id" => 16,
                        "title" => "Write blog post",
                        "description" => "Draft an article on productivity tips",
                        "status" => "todo",
                        "created_at" => "2025-01-19T09:00:00.000Z",
                        "updated_at" => "2025-01-20T08:00:00.000Z"
                    ],
                    [
                        "id" => 17,
                        "title" => "Meditate",
                        "description" => "Spend 10 minutes on mindfulness meditation",
                        "status" => "todo",
                        "created_at" => "2025-01-20T06:10:00.000Z",
                        "updated_at" => "2025-01-20T06:20:00.000Z"
                    ],
                    [
                        "id" => 18,
                        "title" => "Water plants",
                        "description" => "Ensure all indoor plants are watered",
                        "status" => "todo",
                        "created_at" => "2025-01-20T07:00:00.000Z",
                        "updated_at" => "2025-01-20T07:10:00.000Z"
                    ],
                    [
                        "id" => 19,
                        "title" => "Backup files",
                        "description" => "Save important documents to cloud storage",
                        "status" => "todo",
                        "created_at" => "2025-01-19T19:00:00.000Z",
                        "updated_at" => "2025-01-20T09:00:00.000Z"
                    ]
                ],
                [
                    'page' => 2,
                    'limit' => 10
                ],
                0
            ],
            'filtering and sorting' => [
                [
                    [
                        "id" => 0,
                        "title" => "Buy groceries",
                        "description" => "Buy milk, eggs, bread",
                        "status" => "done",
                        "created_at" => "2025-01-20T19:16:35.000Z",
                        "updated_at" => "2025-01-20T19:16:35.000Z"
                    ],
                    [
                        "id" => 1,
                        "title" => "Pay bills",
                        "description" => "Pay electricity and water bills",
                        "status" => "done",
                        "created_at" => "2025-01-21T18:10:22.000Z",
                        "updated_at" => "2025-01-21T19:12:35.000Z"
                    ],
                    [
                        "id" => 2,
                        "title" => "Clean the house",
                        "description" => "Vacuum living room and clean kitchen",
                        "status" => "done",
                        "created_at" => "2025-01-22T17:00:10.000Z",
                        "updated_at" => "2025-01-23T16:10:45.000Z"
                    ],
                    [
                        "id" => 3,
                        "title" => "Workout",
                        "description" => "Go for a 30-minute run",
                        "status" => "done",
                        "created_at" => "2025-01-23T08:20:15.000Z",
                        "updated_at" => "2025-01-23T09:25:45.000Z"
                    ],
                    [
                        "id" => 4,
                        "title" => "Call mom",
                        "description" => "Check in and see how she's doing",
                        "status" => "in progress",
                        "created_at" => "2025-01-24T14:00:00.000Z",
                        "updated_at" => "2025-01-24T14:30:00.000Z"
                    ],
                    [
                        "id" => 5,
                        "title" => "Finish project report",
                        "description" => "Complete and submit the report by evening",
                        "status" => "todo",
                        "created_at" => "2025-01-25T20:10:30.000Z",
                        "updated_at" => "2025-01-25T21:15:30.000Z"
                    ],
                ],
                [
                    'page' => 1,
                    'limit' => 6,
                    'status' => 'done',
                    'sort' => 'created_at',
                ],
                1
            ]
        ];
    }

    public static function singleRetrievalProvider(): array
    {
        return [
            'todo items' => [
                [
                    [
                        "id" => 0,
                        "title" => "Buy groceries",
                        "description" => "Buy milk, eggs, bread",
                        "status" => "todo",
                        "created_at" => "2025-01-20T19:16:35.000Z",
                        "updated_at" => "2025-01-20T19:16:35.000Z"
                    ],
                    [
                        "id" => 1,
                        "title" => "Pay bills",
                        "description" => "Pay electricity and water bills",
                        "status" => "todo",
                        "created_at" => "2025-01-19T18:10:22.000Z",
                        "updated_at" => "2025-01-20T19:12:35.000Z"
                    ],
                    [
                        "id" => 2,
                        "title" => "Clean the house",
                        "description" => "Vacuum living room and clean kitchen",
                        "status" => "done",
                        "created_at" => "2025-01-18T17:00:10.000Z",
                        "updated_at" => "2025-01-19T16:10:45.000Z"
                    ],
                ],
                0
            ],
            'invalid id return false and throws 404' => [
                [
                    [
                        "id" => 0,
                        "title" => "Buy groceries",
                        "description" => "Buy milk, eggs, bread",
                        "status" => "todo",
                        "created_at" => "2025-01-20T19:16:35.000Z",
                        "updated_at" => "2025-01-20T19:16:35.000Z"
                    ],
                    [
                        "id" => 1,
                        "title" => "Pay bills",
                        "description" => "Pay electricity and water bills",
                        "status" => "todo",
                        "created_at" => "2025-01-19T18:10:22.000Z",
                        "updated_at" => "2025-01-20T19:12:35.000Z"
                    ],
                ],
                1
            ],
        ];
    }
}
