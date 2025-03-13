<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class TrimInputMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $parsedBody = $request->getParsedBody();

        if ($parsedBody) {
            $trimmedBody = $this->trimArray($parsedBody);
            $request = $request->withParsedBody($trimmedBody);
        }

        return $handler->handle($request);
    }

    private function trimArray(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->trimArray($value);
            } else {
                $result[$key] = is_string($value) ? trim($value) : $value;
            }
        }

        return $result;
    }
}
