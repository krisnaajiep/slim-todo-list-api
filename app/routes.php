<?php

use Slim\App;
use App\Controllers\AuthController;
use App\Controllers\TodoController;
use Slim\Routing\RouteCollectorProxy;
use Slim\Psr7\Factory\ResponseFactory;
use App\Middlewares\AuthenticationMiddleware;
use App\Middlewares\ContentTypeValidationMiddleware;

return function (App $app) {
    $app->post('/register', [AuthController::class, 'register'])
        ->add(new ContentTypeValidationMiddleware(new ResponseFactory()));
    $app->post('/login', [AuthController::class, 'login'])
        ->add(new ContentTypeValidationMiddleware(new ResponseFactory()));;
    $app->post('/refresh', [AuthController::class, 'refresh'])
        ->add(new AuthenticationMiddleware(new ResponseFactory(), access: false));

    $app->group('/todos', function (RouteCollectorProxy $group) {
        $group->post('', [TodoController::class, 'create'])
            ->add(new ContentTypeValidationMiddleware(new ResponseFactory()));;
        $group->put('/{id}', [TodoController::class, 'update'])
            ->add(new ContentTypeValidationMiddleware(new ResponseFactory()));;
        $group->delete('/{id}', [TodoController::class, 'delete']);
        $group->get('', [TodoController::class, 'index']);
        $group->get('/{id}', [TodoController::class, 'show']);
    })->add(new AuthenticationMiddleware(new ResponseFactory()));
};
