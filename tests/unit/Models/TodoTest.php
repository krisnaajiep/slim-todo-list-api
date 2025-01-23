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
        $id = (string)rand(1, 100);

        $this->db->expects($this->once())
            ->method('lastInsertId')
            ->willReturn($id);

        $result = $this->todo->create($data);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertSame((int)$id, $result['id']);
        $this->assertSame($data['title'], $result['title']);
        $this->assertSame($data['description'], $result['description']);
    }

    #[DataProviderExternal(TodoDataProvider::class, 'modificationProvider')]
    public function testUpdatesTodo(array $data, int $id)
    {
        $this->db->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $this->db->expects($this->exactly(2))
            ->method('fetch')
            ->willReturn(['user_id' => $data['user_id']]);

        if (empty($data['status'])) {
            $data['status'] = 'todo';

            $this->db->expects($this->exactly(2))
                ->method('fetch')
                ->willReturn(['status' => $data['status']]);
        }

        $result = $this->todo->update($id, $data);

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertSame($id, $result['id']);
        $this->assertSame($data['title'], $result['title']);
        $this->assertSame($data['description'], $result['description']);
        $this->assertSame($data['status'], $result['status']);
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

        $this->assertIsBool($result);
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
    public function testGetAllTodoItems(array $data, array $query_params, int $user_id): void
    {
        $page = $query_params['page'];
        $limit = $query_params['limit'];
        $start = $page > 1 ? ($page * $limit) - $limit : 0;

        $new_data = [];

        for ($i = $start; $i < $start + $limit; $i++) {
            $new_data[] = $data[$i];
        }

        if ($user_id === 1) {
            $status = $query_params['status'];
            $sort = $query_params['sort'];

            $new_data = array_filter($new_data, function ($todo) use ($status) {
                return $todo['status'] === $status;
            });

            usort($new_data, function ($a, $b) use ($sort) {
                if ($a[$sort] == $b[$sort]) {
                    return 0;
                }

                return ($a[$sort] > $b[$sort]) ? -1 : 1;
            });
        }

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($new_data);

        $result = $this->todo->getAll($user_id, $start, $limit);

        $this->assertIsArray($result);
        $this->assertCount($user_id === 1 ? 4 : 10, $result);
        $this->assertSame($user_id === 1 ? 3 : 10, $result[0]['id']);
        $this->assertSame($user_id === 1 ? 0 : 19, $result[count($result) - 1]['id']);

        foreach ($result as $key) {
            $this->assertIsArray($key);
            $this->assertCount(6, $key);
            $this->assertArrayHasKey('id', $key);
            $this->assertArrayHasKey('title', $key);
            $this->assertArrayHasKey('description', $key);
            $this->assertArrayHasKey('status', $key);
            $this->assertArrayHasKey('created_at', $key);
            $this->assertArrayHasKey('updated_at', $key);

            if ($user_id === 1) {
                $this->assertSame($status, $key['status']);
            }
        }
    }

    #[DataProviderExternal(TodoDataProvider::class, 'singleRetrievalProvider')]
    public function testGetTodoItemById(array $data, int $user_id): void
    {
        $id = 2;

        $this->db->expects($this->once())
            ->method('fetch')
            ->willReturn($data[$id] ?? false);

        $result = $this->todo->get($id, $user_id);

        if ($user_id === 1) {
            $this->assertFalse($result);
        } else {
            $this->assertIsArray($result);
            $this->assertCount(6, $result);
            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('title', $result);
            $this->assertArrayHasKey('description', $result);
            $this->assertArrayHasKey('status', $result);
            $this->assertArrayHasKey('created_at', $result);
            $this->assertArrayHasKey('updated_at', $result);
            $this->assertSame($data[$id]['id'], $result['id']);
            $this->assertSame($data[$id]['title'], $result['title']);
            $this->assertSame($data[$id]['description'], $result['description']);
            $this->assertSame($data[$id]['status'], $result['status']);
            $this->assertSame($data[$id]['created_at'], $result['created_at']);
            $this->assertSame($data[$id]['updated_at'], $result['updated_at']);
        }
    }

    #[DataProviderExternal(TodoDataProvider::class, 'retrievalProvider')]
    public function testCountTodoItems(array $data, array $query_params, int $user_id): void
    {
        if ($user_id === 1) {
            $this->markTestSkipped('Invalid id throws 404 is not for this test');
        }

        $this->db->expects($this->once())
            ->method('fetch')
            ->willReturn(['COUNT(*)' => count($data)]);

        $result = $this->todo->count($user_id);

        $this->assertIsArray($result);
        $this->assertIsInt($result['COUNT(*)']);
        $this->assertSame(20, $result['COUNT(*)']);
    }
}
