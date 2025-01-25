<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * JsonBodyParserMiddleware
 *
 * This middleware parses the JSON body of incoming HTTP requests and adds the parsed data to the request object.
 * It checks if the 'Content-Type' header is set to 'application/json' and if so, it reads the raw input from 'php://input',
 * decodes the JSON data, and attaches it to the request object using the withParsedBody method.
 * If the JSON data is invalid, it leaves the request object unchanged.
 */
class JsonBodyParserMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response.
     *
     * This middleware checks if the request's Content-Type header is 'application/json'.
     * If so, it decodes the JSON body and adds the parsed data to the request object.
     * The modified request is then passed to the next request handler.
     *
     * @param Request $request The incoming server request.
     * @param RequestHandler $handler The request handler to process the request.
     * @return Response The response from the request handler.
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (strstr($contentType, 'application/json')) {
            $contents = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            }
        }

        return $handler->handle($request);
    }
}
