<?php

use Slim\App;
use App\Controllers\AuthController;
use App\Controllers\TodoController;
use Slim\Routing\RouteCollectorProxy;
use Slim\Psr7\Factory\ResponseFactory;
use App\Middlewares\AuthenticationMiddleware;

return function (App $app) {
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
};
