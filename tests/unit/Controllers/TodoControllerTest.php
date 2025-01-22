<?php

declare(strict_types=1);

use App\Models\Todo;
use Slim\Psr7\Response;
use PHPUnit\Framework\TestCase;
use App\Controllers\TodoController;
use App\JWTHelper;
use Psr\Http\Message\ServerRequestInterface;
use Test\Unit\DataProviders\TodoDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;

final class TodoControllerTest extends TestCase
{
    private ?TodoController $controller;
    private $model, $request, $jwt;
    private ?Response $response;

    public function setUp(): void
    {
        $this->model = $this->createMock(Todo::class);
        $this->jwt = $this->createMock(JWTHelper::class);
        $this->controller = new TodoController($this->model, $this->jwt);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = new Response();
    }

    #[DataProviderExternal(TodoDataProvider::class, 'creationProvider')]
    public function testCreateSuccessfully(array $data): void
    {
        $this->jwt->expects($this->once())
            ->method('decode')
            ->willReturn([
                'sub' => rand(1, 100)
            ]);

        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($data);

        $this->model->expects($this->once())
            ->method('create')
            ->willReturn([
                'id' => rand(1, 100),
                'title' => $data['title'],
                'description' => $data['description']
            ]);

        $response = $this->controller->create($this->request, $this->response, []);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertSame(201, $response->getStatusCode());
    }

    #[DataProviderExternal(TodoDataProvider::class, 'invalidCreationProvider')]
    public function testFailsToCreate(array $data): void
    {
        $this->jwt->expects($this->once())
            ->method('decode')
            ->willReturn([
                'sub' => rand(1, 100)
            ]);

        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($data);

        $response = $this->controller->create($this->request, $this->response, []);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertSame(400, $response->getStatusCode());
    }

    #[DataProviderExternal(TodoDataProvider::class, 'modificationProvider')]
    public function testUpdateSuccessfully(array $data, int $id): void
    {
        $this->jwt->expects($this->once())
            ->method('decode')
            ->willReturn([
                'sub' => $data['user_id']
            ]);

        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($data);

        $this->model->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($id), $this->identicalTo($data))
            ->willReturn([
                'id' => $id,
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'] ?? 'todo'
            ]);

        $response = $this->controller->update($this->request, $this->response, ['id' => $id]);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[DataProviderExternal(TodoDataProvider::class, 'invalidModificationProvider')]
    public function testFailsToUpdate(array $data, $id): void
    {
        $code = $id === 2 ? 404 : ($id === 1 ? 403 : 400);

        $this->jwt->expects($this->once())
            ->method('decode')
            ->willReturn([
                'sub' => 0
            ]);

        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($data);

        if ($code !== 400) {
            $this->model->expects($this->once())
                ->method('update')
                ->with(
                    $this->identicalTo($id),
                    $this->identicalTo($data)
                )->willThrowException(new \Exception('', $code));
        }

        $response = $this->controller->update($this->request, $this->response, ['id' => $id]);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey($code !== 400 ? 'message' : 'errors', $result);

        if ($code === 400) {
            $this->assertArrayHasKey('title', $result['errors']);
            $this->assertArrayHasKey('description', $result['errors']);
            $this->assertArrayHasKey('status', $result['errors']);
        }

        $this->assertSame($code, $response->getStatusCode());
    }

    #[DataProviderExternal(TodoDataProvider::class, 'deletionProvider')]
    public function testDeleteSuccessfully(int $id, int $user_id): void
    {
        $this->jwt->expects($this->once())
            ->method('decode')
            ->willReturn([
                'sub' => $user_id
            ]);

        $this->model->expects($this->once())
            ->method('delete')
            ->with(
                $this->identicalTo($id),
                $this->identicalTo($user_id)
            )->willReturn(true);

        $response = $this->controller->delete($this->request, $this->response, ['id' => $id]);
        $this->assertSame(204, $response->getStatusCode());
    }

    #[DataProviderExternal(TodoDataProvider::class, 'invalidDeletionProvider')]
    public function testFailsToDelete(int $id, int $user_id): void
    {
        $code = $id === 2 ? 404 : 403;

        $this->jwt->expects($this->once())
            ->method('decode')
            ->willReturn([
                'sub' => $user_id
            ]);

        $this->model->expects($this->once())
            ->method('delete')
            ->with(
                $this->identicalTo($id),
                $this->identicalTo($user_id)
            )->willThrowException(new \Exception('', $code));

        $response = $this->controller->delete($this->request, $this->response, ['id' => $id]);
        $this->assertSame($code, $response->getStatusCode());
    }

    #[DataProviderExternal(TodoDataProvider::class, 'retrievalProvider')]
    public function testGetToDoItems(array $data, array $query_params, int $user_id): void
    {
        if ($user_id === 1) {
            $this->markTestSkipped('Invalid id throws 404 is not for this test');
        }

        $this->jwt->expects($this->once())
            ->method('decode')
            ->willReturn([
                'sub' => $user_id
            ]);

        $this->model->expects($this->once())
            ->method('count')
            ->willReturn(['COUNT(*)' => count($data)]);

        $this->request->expects($this->once())
            ->method('getQueryParams')
            ->willReturn($query_params);

        $page = $query_params['page'];
        $limit = $query_params['limit'];
        $start = $page > 1 ? ($page * $limit) - $limit : 0;
        $new_data = [];

        for ($i = $start; $i < $start + $limit; $i++) {
            $new_data[] = $data[$i];
        }

        $this->model->expects($this->once())
            ->method('getAll')
            ->with(
                $this->identicalTo($user_id),
                $this->identicalTo($start),
                $this->identicalTo($limit)
            )->willReturn($new_data);

        $expected = [
            'data' => $new_data,
            'page' => $page,
            'limit' => $limit,
            'total' => count($data)
        ];

        $response = $this->controller->index($this->request, $this->response, []);
        $result = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($result);
        $this->assertSame($expected, $result);
        $this->assertCount(10, $result['data']);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[DataProviderExternal(TodoDataProvider::class, 'retrievalProvider')]
    public function testGetToDoItemById(array $data, array $query_params, int $user_id): void
    {
        $id = 15;

        $this->jwt->expects($this->once())
            ->method('decode')
            ->willReturn([
                'sub' => $user_id
            ]);

        $this->model->expects($this->once())
            ->method('get')
            ->with(
                $this->identicalTo($id),
                $this->identicalTo($user_id),
            )->willReturn($data[$id] ?? false);

        $response = $this->controller->show($this->request, $this->response, ['id' => $id]);
        $result = json_decode((string)$response->getBody(), true);

        if ($user_id === 0) {
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
            $this->assertSame(200, $response->getStatusCode());
        } else {
            $this->assertIsArray($result);
            $this->assertCount(1, $result);
            $this->assertArrayHasKey('message', $result);
            $this->assertSame(404, $response->getStatusCode());
        }
    }
}
