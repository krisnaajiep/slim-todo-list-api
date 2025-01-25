<?php

namespace App\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * The RateLimiterMiddleware class.
 * 
 * This class handles rate limiting middleware operations.
 */
class RateLimiterMiddleware implements MiddlewareInterface
{
    /**
     * Response factory
     * 
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * The rate limit and time frame.
     * 
     * @var int
     */
    private int $limit, $time_frame;

    /**
     * Creates a new RateLimiterMiddleware instance.
     * 
     * @param ResponseFactoryInterface $responseFactory The response factory.
     * @param int $limit The rate limit.
     * @param int $time_frame The time frame.
     */
    public function __construct(ResponseFactoryInterface $responseFactory, $limit = 60, $time_frame = 60)
    {
        $this->responseFactory = $responseFactory;
        $this->limit = $limit;
        $this->time_frame = $time_frame;
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

        // Set path to rate limits cache file.
        $path = __DIR__ . "/../../cache/rate-limits.json";

        // Create the rate limits cache file if it does not exist.
        if (!file_exists($path) || empty(file_get_contents($path))) {
            file_put_contents($path, '[]');
        }

        // Get the rate limits data.
        $data = json_decode(file_get_contents($path), true);

        $limit = $this->limit;
        $time_frame = $this->time_frame;

        // Check if the client IP address exists in the rate limits data.
        if (array_key_exists($address, $data)) {
            // Calculate the elapsed time.
            $elapseTime = time() - $data[$address]['start_time'];

            // Check if the elapsed time is less than the time frame.
            if ($elapseTime < $time_frame) {
                // Check if the rate limit has been reached.
                if ($data[$address]['attempt'] >= $limit) {
                    $message = ['message' => 'Too many requests'];

                    $response = $this->responseFactory->createResponse(429);
                    $response->getBody()->write(json_encode($message));

                    return $response;
                }

                // Update the rate limits data.
                $data[$address]['attempt']++;
                $data[$address]['remaining']--;
            } else {
                // Reset the rate limits data.
                $data[$address] = $this->set($limit, $time_frame);
            }
        } else {
            // Set the rate limits data.
            $data[$address] = $this->set($limit, $time_frame);
        }

        // Save the rate limits data.
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

        return $handler->handle($request);
    }

    /**
     * Set the rate limit data.
     * 
     * @param int $limit The rate limit.
     * @param int $time_frame The time frame.
     * 
     * @return array The rate limit data.
     */
    private function set(int $limit, int $time_frame): array
    {
        return [
            'limit' => $limit,
            'attempt' => 1,
            'start_time' => time(),
            'remaining' => $limit - 1,
            'reset_time' => time() + $time_frame,
        ];
    }
}
