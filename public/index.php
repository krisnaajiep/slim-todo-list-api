<?php

use App\Middlewares\JsonBodyParserMiddleware;
use Middlewares\TrailingSlash;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();

$app->addRoutingMiddleware();

$app->add(new TrailingSlash(trailingSlash: false));
$app->add(new JsonBodyParserMiddleware());

$app->addErrorMiddleware(true, true, true)
    ->getDefaultErrorHandler()
    ->forceContentType('application/json');

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->run();
