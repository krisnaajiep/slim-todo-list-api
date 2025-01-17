<?php

declare(strict_types=1);

use App\Database;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    private ?User $user;
    private $db;

    public function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->user = new User($this->db);
    }

    #[DataProviderExternal(UserDataProvider::class, 'creationProvider')]
    public function testCreated(array $data): void
    {
        $result = $this->user->create($data);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($data['name'], $result['name']);
        $this->assertCount(2, $result);
    }
}

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
