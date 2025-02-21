<?php

namespace App\Controllers;

use App\JWTHelper;
use App\Models\Todo;
use App\Validators\TodoValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;

/**
 * The TodoController class.
 * 
 * This class handles todo operations.
 */
class TodoController
{
    /**
     * The todo model instance for handling todo data operations.
     * 
     * @var Todo
     */
    private Todo $model;

    /**
     * The JWT helper instance for handling JWT token operations.
     * 
     * @var JWTHelper
     */
    private JWTHelper $jwt;

    /**
     * Creates a new TodoController instance.
     * 
     * @param Todo|null $model The todo model instance for handling todo data operations.
     * @param JWTHelper|null $jwt The JWT helper instance for handling JWT token operations.
     */
    public function __construct(Todo $model = null, JWTHelper $jwt = null)
    {
        $this->model = $model ?? new Todo();
        $this->jwt = $jwt ?? new JWTHelper();
    }

    /**
     * Handles getting all todos.
     * 
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The request arguments.
     * 
     * @return Response The response object.
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        // Get the user ID from the JWT token.
        $decoded = $request->getAttribute('decoded_token_data');
        $user_id = $decoded['sub'];

        // Get the total count of todos for the user.
        $count = $this->model->count($user_id)['COUNT(*)'];

        // Get the page, limit, and filters from the query parameters.
        $query_params = $request->getQueryParams();
        $page = $query_params['page'] ?? 1;
        $limit = $query_params['limit'] ?? $count;
        $start = $page > 1 ? ($page * $limit) - $limit : 0;

        // Get the todos with the filters.
        $filters = [
            'status' => $query_params['status'] ?? '',
            'sort' => $query_params['sort'] ?? ''
        ];

        $items = $this->model->getAll($user_id, $start, $limit, $filters);

        $result = [
            'data' => $items,
            'page' => $page,
            'limit' => $limit,
            'total' => $count
        ];

        $response->getBody()->write(json_encode($result));
        return $response;
    }

    /**
     * Handles creating a new todo.
     * 
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The request arguments.
     * 
     * @return Response The response object.
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        // Get the user ID from the JWT token and the request data.
        $decoded = $request->getAttribute('decoded_token_data');
        $data = $request->getParsedBody() ?? [];
        $data['user_id'] = $decoded['sub'];

        // Validate the todo data.
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

    /**
     * Handles getting a todo by ID.
     * 
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The request arguments.
     * 
     * @return Response The response object.
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        // Get the user ID from the JWT token.
        $decoded = $request->getAttribute('decoded_token_data');
        $user_id = $decoded['sub'];

        $todo = $this->model->get($args['id'], $user_id);

        // Check if the todo exists.
        if (!$todo) {
            $code = 404;
            $message = ['message' => 'Todo not found'];
        } else {
            $code = 200;
            $message = $todo;
        }

        $response->getBody()->write(json_encode($message));
        return $response->withStatus($code);
    }

    /**
     * Handles updating a todo by ID.
     * 
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The request arguments.
     * 
     * @return Response The response object.
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        // Get the user ID from the JWT token and the request data.
        $decoded = $request->getAttribute('decoded_token_data');
        $data = $request->getParsedBody() ?? [];
        $data['user_id'] = $decoded['sub'];

        // Validate the todo data.
        $valid_status = ['todo', 'in progress', 'done'];
        $validator = TodoValidator::validate($data);
        if (!empty($data['status']) && !in_array($data['status'], $valid_status)) {
            $validator::setValidationError('status', 'status must be todo, in progress, or done.');
        }
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

    /**
     * Handles deleting a todo by ID.
     * 
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The request arguments.
     * 
     * @return Response The response object.
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        // Get the user ID from the JWT token.
        $decoded = $request->getAttribute('decoded_token_data');
        $user_id = $decoded['sub'];

        try {
            $result = $this->model->delete((int)$args['id'], $user_id);

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
