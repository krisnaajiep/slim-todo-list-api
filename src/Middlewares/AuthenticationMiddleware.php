<?php

namespace App\Middlewares;

use App\JWTHelper;
use App\Models\BlacklistedToken;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthenticationMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private $jwt, $access;

    public function __construct(ResponseFactoryInterface $responseFactory, JWTHelper $jwt = null, bool $access = true)
    {
        $this->responseFactory = $responseFactory;
        $this->jwt = $jwt ?? new JWTHelper();
        $this->access = $access;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $decoded = $this->jwt->decode($request->getHeaderLine('Authorization'));

        if (is_string($decoded) || $decoded['access'] !== $this->access) {
            $message = ['message' => is_string($decoded) && str_contains($decoded, 'Expired') ? $decoded : 'Unauthorized'];

            $response = $this->responseFactory->createResponse(401);
            $response->getBody()->write(json_encode($message));

            return $response;
        }

        return $handler->handle($request);
    }
}
