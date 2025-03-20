<?php

namespace App\Middlewares;

use App\JWTHelper;
use App\Models\User;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * The AuthenticationMiddleware class.
 * 
 * This class handles user authentication middleware operations.
 */
class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * Response factory
     * 
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * The JWT helper instance for handling JWT token operations.
     * 
     * @var JWTHelper
     */
    private JWTHelper $jwt;

    /**
     * The access type.
     * 
     * @var bool
     */
    private bool $access;

    /**
     * The user model instance.
     * 
     * @var User
     */
    private User $model;

    /**
     * Creates a new AuthenticationMiddleware instance.
     * 
     * @param ResponseFactoryInterface $responseFactory The response factory.
     * @param JWTHelper|null $jwt The JWT helper instance for handling JWT token operations.
     * @param bool $access The access type.
     */
    public function __construct(ResponseFactoryInterface $responseFactory, JWTHelper $jwt = null, bool $access = true)
    {
        $this->responseFactory = $responseFactory;
        $this->jwt = $jwt ?? new JWTHelper();
        $this->access = $access;
        $this->model = new User();
    }

    /**
     * Process an incoming server request.
     * 
     * @param Request $request The request object.
     * @param RequestHandler $handler The request handler.
     * 
     * @return Response The response object.
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $auth = $request->getHeaderLine('Authorization');
        $directives = explode(' ', $auth);
        $scheme = $directives[0];
        $jwt = $directives[1] ?? '';

        // Check if the scheme is Bearer and the JWT token exists.
        if ($scheme !== 'Bearer' || !$jwt) {
            return $this->jsonErrorResponse('Unauthorized');
        }

        // Decode the JWT token.
        $decoded = $this->jwt->decode($jwt);

        // Find the user by the decoded token data.
        $user = $this->model->find($decoded['sub'] ?? 0);

        // Check if the token is invalid or expired.
        if (is_string($decoded) || $decoded['access'] !== $this->access || !$user['exists']) {
            $message = is_string($decoded) && str_contains($decoded, 'Expired') ? $decoded : 'Unauthorized';

            return $this->jsonErrorResponse($message);
        }

        $request = $request->withAttribute('decoded_token_data', $decoded);

        return $handler->handle($request);
    }

    /**
     * Generate a JSON-formatted error response.
     *
     * @param string $message The error message to be included in the response.
     * @param integer $code The HTTP status code for the response.
     * @return Response The generated JSON response with the specified error message and status code.
     */
    private function jsonErrorResponse(string $message, int $code = 401): Response
    {
        $response = $this->responseFactory->createResponse($code);
        $response->getBody()->write(json_encode(['message' => $message]));

        return $response;
    }
}
