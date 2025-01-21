<?php

use App\Controllers\AuthController;
use App\Controllers\TodoController;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Middlewares\TrailingSlash;
use App\Handlers\ShutdownHandler;
use App\Handlers\HttpErrorHandler;
use App\Middlewares\AuthenticationMiddleware;
use App\Middlewares\ReturningJsonMiddleware;
use App\Middlewares\JsonBodyParserMiddleware;
use Slim\Factory\ServerRequestCreatorFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set that to your needs
$displayErrorDetails = true;

$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();

$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Handling Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Middlewares
$app->add(new TrailingSlash(trailingSlash: false));
$app->add(new JsonBodyParserMiddleware());
$app->add(new ReturningJsonMiddleware());

// Routes
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write(json_encode(["Foo" => "Bar"]));
    return $response;
});

$app->post('/register', [AuthController::class, 'register']);
$app->post('/login', [AuthController::class, 'login']);

$app->group('/todos', function (RouteCollectorProxy $group) {
    $group->post('', [TodoController::class, 'create']);
    $group->put('/{id}', [TodoController::class, 'update']);
    $group->delete('/{id}', [TodoController::class, 'delete']);
    $group->get('', [TodoController::class, 'index']);
})->add(new AuthenticationMiddleware(new ResponseFactory()));

$app->run();
