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
    public function testRegistersSuccessfully(array $data): void
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

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertSame(3600, $result['expires_in']);
        $this->assertSame(201, $response->getStatusCode());
    }

    #[DataProviderExternal(UserDataProvider::class, 'invalidRegistrationProvider')]
    public function testFailsToRegister(array $data, string $case): void
    {
        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($data);

        if ($case === 'duplicate email') {
            $this->model->expects($this->once())
                ->method('create')
                ->willThrowException(new Exception('Email already exists', 409));
        }

        $response = $this->controller->register($this->request, $this->response, []);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);

        if ($case === 'invalid registration data') {
            $this->assertArrayHasKey('errors', $result);
            $this->assertCount(3, $result['errors']);
            $this->assertArrayHasKey('name', $result['errors']);
            $this->assertArrayHasKey('email', $result['errors']);
            $this->assertArrayHasKey('password_confirmation', $result['errors']);
            $this->assertSame('name field is required.', $result['errors']['name']);
            $this->assertSame('email input must be a valid email address.', $result['errors']['email']);
            $this->assertSame("password_confirmation doesn't match.", $result['errors']['password_confirmation']);
            $this->assertSame(400, $response->getStatusCode());
        } else {
            $this->assertArrayHasKey('message', $result);
            $this->assertSame('Email already exists', $result['message']);
            $this->assertSame(409, $response->getStatusCode());
        }
    }

    #[DataProviderExternal(UserDataProvider::class, 'validAuthenticationProvider')]
    public function testLogInSuccessfully(array $credentials): void
    {
        $user = [
            'id' => rand(1, 100),
            'name' => 'John Doe'
        ];

        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($credentials);

        $this->model->expects($this->once())
            ->method('authenticate')
            ->willReturn($user);

        $response = $this->controller->login($this->request, $this->response, []);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertSame(3600, $result['expires_in']);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[DataProviderExternal(UserDataProvider::class, 'invalidAuthenticationProvider')]
    public function testFailsToLogIn(array $credentials, string $case): void
    {
        $this->request->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($credentials);

        if ($case === 'incorrect password') {
            $this->model->expects($this->once())
                ->method('authenticate')
                ->willThrowException(new Exception('Invalid email or password.', 401));
        }

        $response = $this->controller->login($this->request, $this->response, []);

        $result = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);

        if ($case === 'invalid credentials') {
            $this->assertArrayHasKey('errors', $result);
            $this->assertCount(2, $result['errors']);
            $this->assertArrayHasKey('email', $result['errors']);
            $this->assertArrayHasKey('password', $result['errors']);
            $this->assertSame('email input must be a valid email address.', $result['errors']['email']);
            $this->assertSame('password field is required.', $result['errors']['password']);
            $this->assertSame(400, $response->getStatusCode());
        } else {
            $this->assertArrayHasKey('message', $result);
            $this->assertSame('Invalid email or password.', $result['message']);
            $this->assertSame(401, $response->getStatusCode());
        }
    }
}
