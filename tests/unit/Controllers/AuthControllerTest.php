<?php

declare(strict_types=1);

use App\Models\User;
use Slim\Psr7\Response;
use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use Psr\Http\Message\ServerRequestInterface;
use Test\Unit\DataProviders\UserDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;

final class AuthControllerTest extends TestCase
{
    private ?AuthController $controller;
    private $model, $request;
    private ?Response $response;

    public function setUp(): void
    {
        $this->model = $this->createMock(User::class);
        $this->controller = new AuthController($this->model);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = new Response();
    }

    #[DataProviderExternal(UserDataProvider::class, 'validRegistrationProvider')]
    public function testRegistrated(array $data): void
    {
        $created = [
            'id' => rand(1, 100),
            'name' => $data['name'],
        ];

        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($data);

        $this->model->expects($this->once())
            ->method('create')
            ->willReturn($created);

        $response = $this->controller->register($this->request, $this->response, []);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertSame(201, $response->getStatusCode());
    }

    #[DataProviderExternal(UserDataProvider::class, 'invalidRegistrationProvider')]
    public function testNotRegistrated(array $data): void
    {
        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($data);

        $response = $this->controller->register($this->request, $this->response, []);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertArrayHasKey('errors', $result);
        $this->assertSame(400, $response->getStatusCode());
    }

    #[DataProviderExternal(UserDataProvider::class, 'validRegistrationProvider')]
    public function testNotRegistratedDuplicateEmail(array $data): void
    {
        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($data);

        $this->model->expects($this->once())
            ->method('create')
            ->willThrowException(new Exception('Email already exists', 409));

        $response = $this->controller->register($this->request, $this->response, []);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertArrayHasKey('message', $result);
        $this->assertSame('Email already exists', $result['message']);
        $this->assertSame(409, $response->getStatusCode());
    }
}
