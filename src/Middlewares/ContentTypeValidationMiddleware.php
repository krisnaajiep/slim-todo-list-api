<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * The ContentTypeValidation class.
 * 
 * This class handles Content-Type request header validation middleware operations.
 */
class ContentTypeValidationMiddleware implements MiddlewareInterface
{
    /**
     * Response factory
     * 
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * Creates a new ContentTypeValidationMiddleware instance.
     * 
     * @param ResponseFactoryInterface $responseFactory The response factory.
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
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
        $contentType = $request->getHeaderLine('Content-Type');

        if (empty($contentType)) {
            return $this->jsonErrorResponse('Content-Type header is required', 400);
        }

        $contentType = strtolower(trim(strtok($contentType, ';')));

        if ($contentType !== 'application/json') {
            return $this->jsonErrorResponse("Invalid Content-Type. Use 'application/json'.", 415);
        }

        return $handler->handle($request);
    }


    /**
     * Generate a JSON-formatted error response.
     *
     * @param string $message The error message to be included in the response.
     * @param integer $code The HTTP status code for the response.
     * @return Response The generated JSON response with the specified error message and status code.
     */
    private function jsonErrorResponse(string $message, int $code): Response
    {
        $response = $this->responseFactory->createResponse($code);
        $response->getBody()->write(json_encode(['message' => $message]));

        return $response;
    }
}
