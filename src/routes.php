<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

//$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
//    // Sample log message
//    $this->logger->info("Slim-Skeleton '/' route");
//    // Render index view
//    return $this->view->render($response, 'index.phtml', $args);
//});
$app->get('/login', '\App\Controller\LoginController:index');
//$app->get('/home', '\App\Controller\HomeController:index')->add(new \App\MIddleware\Auth);
$app->get('/groupPush/{gp}', '\App\Controller\GroupPushController:index');

$app->group('/admin', function () {
//    $this->get('/login', '\App\Controller\LoginController:index');
})->add(new \App\MIddleware\Auth);
