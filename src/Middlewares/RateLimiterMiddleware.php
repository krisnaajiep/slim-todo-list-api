<?php

namespace App\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class RateLimiterMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private int $limit, $time_frame;

    public function __construct(ResponseFactoryInterface $responseFactory, $limit = 60, $time_frame = 60)
    {
        $this->responseFactory = $responseFactory;
        $this->limit = $limit;
        $this->time_frame = $time_frame;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $params = $request->getServerParams();
        $address = $params['REMOTE_ADDR'];
        $path = __DIR__ . "/../../cache/rate-limits.json";

        if (!file_exists($path) || empty(file_get_contents($path))) {
            file_put_contents($path, '[]');
        }

        $data = json_decode(file_get_contents($path), true);

        $limit = $this->limit;
        $time_frame = $this->time_frame;

        if (array_key_exists($address, $data)) {
            $elapseTime = time() - $data[$address]['start_time'];

            if ($elapseTime < $time_frame) {
                if ($data[$address]['attempt'] >= $limit) {
                    $message = ['message' => 'Too many requests'];

                    $response = $this->responseFactory->createResponse(429);
                    $response->getBody()->write(json_encode($message));

                    return $response;
                }

                $data[$address]['attempt']++;
                $data[$address]['remaining']--;
            } else {
                $data[$address] = $this->set($limit, $time_frame);
            }
        } else {
            $data[$address] = $this->set($limit, $time_frame);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

        return $handler->handle($request);
    }

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
