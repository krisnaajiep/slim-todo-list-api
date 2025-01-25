<?php

namespace App\Middlewares;

use App\JWTHelper;
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
        // Decode the JWT token.
        $decoded = $this->jwt->decode($request->getHeaderLine('Authorization'));

        // Check if the token is invalid or expired.
        if (is_string($decoded) || $decoded['access'] !== $this->access) {
            $message = ['message' => is_string($decoded) && str_contains($decoded, 'Expired') ? $decoded : 'Unauthorized'];

            $response = $this->responseFactory->createResponse(401);
            $response->getBody()->write(json_encode($message));

            return $response;
        }

        return $handler->handle($request);
    }
}
