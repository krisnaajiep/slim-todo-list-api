<?php

namespace App\Middlewares;

use App\JWTHelper;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthenticationMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $decoded = (new JWTHelper())->decode($request->getHeaderLine('Authorization'));

        if (is_string($decoded)) {
            $message = ['message' => !$decoded ? 'Unauthorized' : $decoded];

            $response = $this->responseFactory->createResponse(401);
            $response->getBody()->write(json_encode($message));

            return $response;
        }

        return $handler->handle($request);
    }
}
