<?php

declare(strict_types=1);

use App\Database;
use App\Models\Todo;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Test\Unit\DataProviders\TodoDataProvider;

final class TodoTest extends TestCase
{
    private ?Todo $todo;
    private $db;

    public function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->todo = new Todo($this->db);
    }

    #[DataProviderExternal(TodoDataProvider::class, 'creationProvider')]
    public function testCreatesTodo(array $data): void
    {
        $result = $this->todo->create($data);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('description', $result);
    }
}
