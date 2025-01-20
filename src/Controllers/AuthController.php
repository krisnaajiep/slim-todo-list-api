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
    private $model;

    public function __construct(User $model = null)
    {
        $this->model = $model ?? new User();
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

            $response->getBody()->write(json_encode($this->respondWithToken($user)));
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

            $response->getBody()->write(json_encode($this->respondWithToken($user)));
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

    private function respondWithToken(array $user = [])
    {
        $ttl = 3600;

        $jwt = (new JWTHelper())->encode($user, $ttl, true);

        return [
            'access_token' => $jwt,
            'expires_in' => $ttl,
        ];
    }
}
