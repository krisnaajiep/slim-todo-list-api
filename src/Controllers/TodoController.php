<?php

namespace App\Controllers;

use App\JWTHelper;
use App\Models\Todo;
use App\Validators\TodoValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TodoController
{
    private $model, $jwt;

    public function __construct(Todo $model = null, JWTHelper $jwt = null)
    {
        $this->model = $model ?? new Todo();
        $this->jwt = $jwt ?? new JWTHelper();
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $decoded = $this->jwt->decode($request->getHeaderLine('Authorization'));
        $data = $request->getParsedBody() ?? [];
        $data['user_id'] = $decoded['sub'];

        $validator = TodoValidator::validate($data);
        if ($validator->hasValidationErrors()) {
            $errors = ['errors' => $validator->getValidationErrors()];

            $response->getBody()->write(json_encode($errors));
            return $response->withStatus(400);
        }

        $todo = $this->model->create($data);

        $response->getBody()->write(json_encode($todo));
        return $response->withStatus(201);
    }
}
