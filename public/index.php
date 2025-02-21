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
use App\Middlewares\RateLimiterMiddleware;
use App\Middlewares\ThrottlingMiddleware;
use Slim\Factory\ServerRequestCreatorFactory;
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

// Parse json, form data and xml
$app->addBodyParsingMiddleware();

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Handling Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Add Middlewares
$app->add(new ReturningJsonMiddleware());
$app->add(new RateLimiterMiddleware(new ResponseFactory(), 60, 60));
$app->add(new ThrottlingMiddleware(1));
$app->add(new TrailingSlash(trailingSlash: false));

// Add Routes
$app->post('/register', [AuthController::class, 'register']);
$app->post('/login', [AuthController::class, 'login']);
$app->post('/refresh', [AuthController::class, 'refresh'])
    ->add(new AuthenticationMiddleware(new ResponseFactory(), access: false));

$app->group('/todos', function (RouteCollectorProxy $group) {
    $group->post('', [TodoController::class, 'create']);
    $group->put('/{id}', [TodoController::class, 'update']);
    $group->delete('/{id}', [TodoController::class, 'delete']);
    $group->get('', [TodoController::class, 'index']);
    $group->get('/{id}', [TodoController::class, 'show']);
})->add(new AuthenticationMiddleware(new ResponseFactory()));

$app->run();
