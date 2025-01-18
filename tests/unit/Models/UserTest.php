<?php

declare(strict_types=1);

use App\Database;
use App\Models\User;
use PHPUnit\Framework\TestCase;
use Test\Unit\DataProviders\UserDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;

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

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertSame($data['name'], $result['name']);
    }

    #[DataProviderExternal(UserDataProvider::class, 'validAuthenticationProvider')]
    public function testAuthenticated(array $credentials): void
    {
        $data = (new UserDataProvider())->validRegistrationProvider()['valid registration data'][0];
        $data['id'] = rand(1, 100);
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $this->db->expects($this->once())
            ->method('fetch')
            ->willReturn($data);

        $this->db->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $result = $this->user->authenticate($credentials);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertSame($data['name'], $result['name']);
    }

    #[DataProviderExternal(UserDataProvider::class, 'invalidAuthenticationProvider')]
    public function testUnauthenticated(array $credentials): void
    {
        $data = (new UserDataProvider())->validRegistrationProvider()['valid registration data'][0];
        $data['id'] = rand(1, 100);
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $this->db->expects($this->once())
            ->method('fetch')
            ->willReturn($data);

        $this->db->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $this->expectException(\Exception::class);

        $this->user->authenticate($credentials);
    }
}
