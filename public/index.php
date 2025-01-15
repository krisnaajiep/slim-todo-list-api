<?php

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Middlewares\TrailingSlash;
use App\Middlewares\ReturningJsonMiddleware;
use App\Middlewares\JsonBodyParserMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = AppFactory::create();

$app->addRoutingMiddleware();

$app->addErrorMiddleware(true, true, true)
    ->getDefaultErrorHandler()
    ->forceContentType('application/json');

$app->add(new TrailingSlash(trailingSlash: false));
$app->add(new JsonBodyParserMiddleware());
$app->add(new ReturningJsonMiddleware());

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write(json_encode(["Foo" => "Bar"]));
    return $response;
});

$app->run();
