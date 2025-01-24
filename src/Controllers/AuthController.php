<?php

namespace App\Controllers;

use App\JWTHelper;
use App\Models\User;
use App\Validators\UserLoginValidator;
use App\Validators\UserRegisterValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;

class AuthController
{
    private $model, $jwt;

    public function __construct(User $model = null, JWTHelper $jwt = null)
    {
        $this->model = $model ?? new User();
        $this->jwt = $jwt ?? new JWTHelper();
    }

    public function register(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody() ?? [];

        $validator = UserRegisterValidator::validate($data);
        if ($validator->hasValidationErrors()) {
            $errors = ['errors' => $validator->getValidationErrors()];

            $response->getBody()->write(json_encode($errors));
            return $response->withStatus(400);
        }

        try {
            $user = $this->model->create($data);

            $response->getBody()->write(json_encode($this->respondWithTokens($user)));
            return $response->withStatus(201);
        } catch (\Throwable $th) {
            if ($th->getCode() == 409) {
                $message = ['message' => $th->getMessage()];

                $response->getBody()->write(json_encode($message));
                return $response->withStatus(409);
            } else {
                throw new HttpInternalServerErrorException($request);
            }
        }
    }

    public function login(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody() ?? [];

        $validator = UserLoginValidator::validate($data);
        if ($validator->hasValidationErrors()) {
            $errors = ['errors' => $validator->getValidationErrors()];

            $response->getBody()->write(json_encode($errors));
            return $response->withStatus(400);
        }

        try {
            $user = $this->model->authenticate($data);

            $response->getBody()->write(json_encode($this->respondWithTokens($user)));
            return $response;
        } catch (\Throwable $th) {
            if ($th->getCode() == 401) {
                $message = ['message' => $th->getMessage()];

                $response->getBody()->write(json_encode($message));
                return $response->withStatus(401);
            } else {
                throw new HttpInternalServerErrorException($request);
            }
        }
    }

    public function refresh(Request $request, Response $response, array $args): Response
    {
        $decoded = $this->jwt->decode($request->getHeaderLine('Authorization'));

        $user = [
            'id' => $decoded['sub'],
            'name' => $decoded['name']
        ];

        $response->getBody()->write(json_encode($this->respondWithOnlyAccessToken($user)));
        return $response;
    }

    private function respondWithTokens(array $user = []): array
    {
        $ttl = 3600;
        $refresh_ttl = $ttl * 24 * 3;

        $access_token = $this->jwt->encode($user, $ttl, true);
        $refresh_token = $this->jwt->encode($user, $refresh_ttl, false);

        return [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
        ];
    }

    private function respondWithOnlyAccessToken(array $user = []): array
    {
        $ttl = 3600;

        $access_token = $this->jwt->encode($user, $ttl, true);

        return [
            'message' => 'Token refreshed',
            'access_token' => $access_token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
        ];
    }
}
