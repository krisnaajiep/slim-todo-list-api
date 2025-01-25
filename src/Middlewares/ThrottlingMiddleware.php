<?php

namespace App\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * The ThrottlingMiddleware class.
 * 
 * This class handles throttling middleware operations.
 */
class ThrottlingMiddleware implements MiddlewareInterface
{
    /**
     * The minimum interval between requests.
     * 
     * @var int
     */
    private int $min_interval;

    /**
     * Creates a new ThrottlingMiddleware instance.
     * 
     * @param int $min_interval The minimum interval between requests.
     */
    public function __construct(int $min_interval = 1)
    {
        $this->min_interval = $min_interval;
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
        // Get the client IP address.
        $params = $request->getServerParams();
        $address = $params['REMOTE_ADDR'];

        // Set path to throttles cache file.
        $path = __DIR__ . "/../../cache/throttles.json";

        // Create the throttles cache file if it does not exist.
        if (!file_exists($path) || empty(file_get_contents($path))) {
            file_put_contents($path, '[]');
        }

        // Get the throttles cache data.
        $data = json_decode(file_get_contents($path), true);

        // Set the minimum interval between requests.
        $min_interval = $this->min_interval;

        // Throttle the request if the minimum interval has not elapsed.
        if (array_key_exists($address, $data) && microtime(true) - $data[$address] < $min_interval) {
            sleep($min_interval);
        } else {
            $data[$address] = microtime(true);
            file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        }

        return $handler->handle($request);
    }
}
