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
     * It also adds the 'X-RateLimit-Limit', 'X-RateLimit-Remaining', and 'X-RateLimit-Reset' headers to the response.
     * 
     * @param Request $request The incoming server request.
     * @param RequestHandler $handler The request handler to process the request.
     * @return Response The response with the added JSON content type header.
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        // Return JSON
        $response = $response
            ->withAddedHeader('Content-Type', 'application/json')
            ->withHeader('X-Content-Type-Options', 'nosniff');

        // Rate Limit Headers
        $params = $request->getServerParams();
        $address = $params['REMOTE_ADDR'];

        $path = __DIR__ . '/../../cache/rate-limits.json';

        $rate_limit = json_decode(file_get_contents($path), true);
        $limit = $rate_limit[$address]['limit'];
        $remaining = $rate_limit[$address]['remaining'];
        $reset_time = $rate_limit[$address]['reset_time'];

        $response = $response
            ->withHeader('X-RateLimit-Limit', $limit)
            ->withHeader('X-RateLimit-Remaining', $remaining)
            ->withHeader('X-RateLimit-Reset', $reset_time);

        return $response;
    }
}
