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

    #[DataProviderExternal(TodoDataProvider::class, 'modificationProvider')]
    public function testUpdatesTodo(array $data, int $id)
    {
        $this->db->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $this->db->expects($this->once())
            ->method('fetch')
            ->willReturn(['user_id' => $data['user_id']]);

        $result = $this->todo->update($id, $data);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('description', $result);
    }

    #[DataProviderExternal(TodoDataProvider::class, 'invalidModificationProvider')]
    public function testFailsToUpdateTodo(array $data, int $id)
    {
        if (!$id) {
            $this->markTestSkipped('Invalid todo data is not for this test');
        }

        $rowCount = $id === 2 ? 0 : 1;

        $this->db->expects($this->once())
            ->method('rowCount')
            ->willReturn($rowCount);

        if (!$rowCount) {
            $this->expectExceptionCode(404);
        } else {
            $this->db->expects($this->once())
                ->method('fetch')
                ->willReturn(['user_id' => $id]);

            $this->expectExceptionCode(403);
        }

        $this->todo->update($id, $data);
    }

    #[DataProviderExternal(TodoDataProvider::class, 'deletionProvider')]
    public function testDeletesTodo(int $id, int $user_id): void
    {
        $this->db->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $this->db->expects($this->once())
            ->method('fetch')
            ->willReturn(['user_id' => $id]);

        $this->db->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);

        $result = $this->todo->delete($id, $user_id);

        $this->assertTrue($result);
    }

    #[DataProviderExternal(TodoDataProvider::class, 'invalidDeletionProvider')]
    public function testFailsToDeleteTodo(int $id, $user_id): void
    {
        $rowCount = $id === 2 ? 0 : 1;

        $this->db->expects($this->once())
            ->method('rowCount')
            ->willReturn($rowCount);

        if (!$rowCount) {
            $this->expectExceptionCode(404);
        } else {
            $this->db->expects($this->once())
                ->method('fetch')
                ->willReturn(['user_id' => $id]);

            $this->expectExceptionCode(403);
        }

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->todo->delete($id, $user_id);
    }

    #[DataProviderExternal(TodoDataProvider::class, 'retrievalProvider')]
    public function testGetAllTodoItems(array $data, int $user_id): void
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($data);

        $result = $this->todo->getAll($user_id);

        $this->assertIsArray($result);
        $this->assertCount(count($data), $result);
        $this->assertSame($data, $result);
    }
}
