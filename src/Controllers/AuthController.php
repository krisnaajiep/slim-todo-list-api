<?php

namespace App\Controllers;

use App\JWTHelper;
use App\Models\User;
use App\Validators\UserLoginValidator;
use App\Validators\UserRegisterValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;

/**
 * The AuthController class.
 * 
 * This class handles user authentication operations.
 */
class AuthController
{
    /**
     * The user model instance for handling user data operations.
     * 
     * @var User
     */
    private User $model;

    /**
     * The JWT helper instance for handling JWT token operations.
     * 
     * @var JWTHelper
     */
    private JWTHelper $jwt;

    /**
     * Creates a new AuthController instance.
     * 
     * @param User|null $model The user model instance for handling user data operations.
     * @param JWTHelper|null $jwt The JWT helper instance for handling JWT token operations.
     */
    public function __construct(User $model = null, JWTHelper $jwt = null)
    {
        $this->model = $model ?? new User();
        $this->jwt = $jwt ?? new JWTHelper();
    }

    /**
     * Handles user registration.
     * 
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The request arguments.
     * 
     * @return Response The response object.
     */
    public function register(Request $request, Response $response, array $args): Response
    {
        // Get the request data.
        $data = $request->getParsedBody() ?? [];

        // Validate the request data.
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
            // Handle the case where the user email already exists.
            if ($th->getCode() == 409) {
                $message = ['message' => $th->getMessage()];

                $response->getBody()->write(json_encode($message));
                return $response->withStatus(409);
            } else {
                throw new HttpInternalServerErrorException($request);
            }
        }
    }

    /**
     * Handles user login.
     * 
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The request arguments.
     * 
     * @return Response The response object.
     */
    public function login(Request $request, Response $response, array $args): Response
    {
        // Get the request data.
        $data = $request->getParsedBody() ?? [];

        // Validate the request data.
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
            // Handle the case where the user email or password is incorrect.
            if ($th->getCode() == 401) {
                $message = ['message' => $th->getMessage()];

                $response->getBody()->write(json_encode($message));
                return $response->withStatus(401);
            } else {
                throw new HttpInternalServerErrorException($request);
            }
        }
    }

    /**
     * Handles token refresh.
     * 
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $args The request arguments.
     * 
     * @return Response The response object.
     */
    public function refresh(Request $request, Response $response, array $args): Response
    {
        // Get the user ID and name from the JWT token.
        $decoded = $this->jwt->decode($request->getHeaderLine('Authorization'));

        $user = [
            'id' => $decoded['sub'],
            'name' => $decoded['name']
        ];

        $response->getBody()->write(json_encode($this->respondWithOnlyAccessToken($user)));
        return $response;
    }

    /**
     * Generates access and refresh tokens.
     * 
     * @param array $user The user data.
     * 
     * @return array The tokens.
     */
    private function respondWithTokens(array $user = []): array
    {
        // The access token expires in 1 hour.
        $ttl = 3600;

        // The refresh token expires in 3 days.
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

    /**
     * Generates only an access token.
     * 
     * @param array $user The user data.
     * 
     * @return array The access token.
     */
    private function respondWithOnlyAccessToken(array $user = []): array
    {
        // The access token expires in 1 hour.
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
