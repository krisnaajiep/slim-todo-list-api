<?php

namespace App\Controllers;

use App\JWTHelper;
use App\Models\Todo;
use App\Validators\TodoValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;

class TodoController
{
    private $model, $jwt;

    public function __construct(Todo $model = null, JWTHelper $jwt = null)
    {
        $this->model = $model ?? new Todo();
        $this->jwt = $jwt ?? new JWTHelper();
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        $decoded = $this->jwt->decode($request->getHeaderLine('Authorization'));
        $user_id = $decoded['sub'];

        $count = $this->model->count($user_id)['COUNT(*)'];

        $query_params = $request->getQueryParams();
        $page = $query_params['page'] ?? 1;
        $limit = $query_params['limit'] ?? $count;
        $start = $page > 1 ? ($page * $limit) - $limit : 0;

        $items = $this->model->getAll($user_id, $start, $limit);

        $result = [
            'data' => $items,
            'page' => $page,
            'limit' => $limit,
            'total' => $count
        ];

        $response->getBody()->write(json_encode($result));
        return $response;
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

    public function update(Request $request, Response $response, array $args): Response
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

        try {
            $todo = $this->model->update((int)$args['id'], $data);

            $response->getBody()->write(json_encode($todo));
            return $response;
        } catch (\Throwable $th) {
            if ($th->getCode() != 500) {
                $message = ['message' => $th->getMessage()];

                $response->getBody()->write(json_encode($message));
                return $response->withStatus($th->getCode());
            } else {
                throw new HttpInternalServerErrorException($request);
            }
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $decoded = $this->jwt->decode($request->getHeaderLine('Authorization'));

        try {
            $result = $this->model->delete((int)$args['id'], $decoded['sub']);

            if ($result) {
                return $response->withStatus(204);
            }
        } catch (\Throwable $th) {
            if ($th->getCode() != 500) {
                $message = ['message' => $th->getMessage()];

                $response->getBody()->write(json_encode($message));
                return $response->withStatus($th->getCode());
            } else {
                throw new HttpInternalServerErrorException($request);
            }
        }
    }
}
