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
                'description' => $data['description']
            ]);

        $response = $this->controller->update($this->request, $this->response, ['id' => $id]);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('description', $result);
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
        $this->assertSame($code, $response->getStatusCode());
    }
}
