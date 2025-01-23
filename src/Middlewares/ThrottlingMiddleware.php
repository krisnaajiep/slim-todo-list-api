<?php

namespace App\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ThrottlingMiddleware implements MiddlewareInterface
{
    private int $min_interval;

    public function __construct(int $min_interval = 1)
    {
        $this->min_interval = $min_interval;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $params = $request->getServerParams();
        $address = $params['REMOTE_ADDR'];
        $path = __DIR__ . "/../../cache/throttles.json";

        if (!file_exists($path) || empty(file_get_contents($path))) {
            file_put_contents($path, '[]');
        }

        $data = json_decode(file_get_contents($path), true);

        $min_interval = $this->min_interval;

        if (array_key_exists($address, $data) && microtime(true) - $data[$address] < $min_interval) {
            sleep($min_interval);
        } else {
            $data[$address] = microtime(true);
            file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        }

        return $handler->handle($request);
    }
}
