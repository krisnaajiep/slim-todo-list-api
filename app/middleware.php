<?php

use Slim\App;
use Middlewares\TrailingSlash;
use Slim\Psr7\Factory\ResponseFactory;
use App\Middlewares\TrimInputMiddleware;
use App\Middlewares\ThrottlingMiddleware;
use App\Middlewares\RateLimiterMiddleware;
use App\Middlewares\ReturningJsonMiddleware;

return function (App $app) {
    $app->add(new ReturningJsonMiddleware());
    $app->add(new TrimInputMiddleware());

    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    $app->add(new RateLimiterMiddleware(new ResponseFactory(), 60, 60));
    $app->add(new ThrottlingMiddleware(1));
    $app->add(new TrailingSlash(trailingSlash: false));
};
