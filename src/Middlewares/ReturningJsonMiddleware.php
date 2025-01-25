<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


/**
 * ReturningJsonMiddleware
 *
 * This middleware ensures that the response from the request handler
 * has the 'Content-Type' header set to 'application/json'.
 *
 * Implements MiddlewareInterface.
 *
 * @package App\Middlewares
 */
class ReturningJsonMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, adding a JSON content type header.
     *
     * This middleware intercepts the request, processes it through the next handler, and ensures that
     * the response has the 'Content-Type' header set to 'application/json'.
     *
     * @param Request $request The incoming server request.
     * @param RequestHandler $handler The request handler to process the request.
     * @return Response The response with the added JSON content type header.
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $response = $response->withAddedHeader('Content-Type', 'application/json');

        return $response;
    }
}
