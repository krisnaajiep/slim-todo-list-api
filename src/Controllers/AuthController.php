<?php

namespace App\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use KrisnaAjieP\PHPValidator\Validator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private $model;

    public function __construct(User $model = null)
    {
        $this->model = $model;
    }

    public function register(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        $validator = Validator::setRules($data, [
            'name' => ['required', 'alpha', 'min_length:2', 'max_length:50'],
            'email' => ['required', 'email', 'max_length:254'],
            'password' => ['required', 'min_length:8', 'max_length:255'],
            'password_confirmation' => ['required', 'match:password'],
        ]);

        if ($validator->hasValidationErrors()) {
            $errors = ['errors' => $validator->getValidationErrors()];

            $response->getBody()->write(json_encode($errors));
            return $response->withStatus(400);
        }

        $user = $this->model->create($data);

        $response->getBody()->write(json_encode($this->respondWithToken($user)));
        return $response->withStatus(201);
    }

    private function respondWithToken(array $user = [])
    {
        $key = 'exampe_key';
        $exp = 3600;

        $payload = [
            'iat' => time(),
            'exp' => time() + $exp,
            'jti' => bin2hex(random_bytes(16)),
            'sub' => $user['id'],
            'name' => $user['name'],
            'access' => true,
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');

        return [
            'access_token' => $jwt,
            'expires_in' => $exp,
        ];
    }
}
