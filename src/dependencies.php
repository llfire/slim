<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
//$container['renderer'] = function ($c) {
//    $settings = $c->get('settings')['renderer'];
//    return new Slim\Views\PhpRenderer($settings['template_path']);
//};
$container['view'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        'cache' => $settings['cache']
    ]);
    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new Slim\Views\TwigExtension($router, $uri));

    return $view;
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

//db
//$container['db'] = function ($c) {
//    $db = $c->get('settings')['db'];
//    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
//    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
//    return $pdo;
//};

$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);
    $capsule->addConnection($container['settings']['db_stack'], 'db_stack');
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    return $capsule;
};

//$container[App\Controller\HomeController::class] = function ($c) {
//    $view = $c->get('view');
//    $logger = $c->get('logger');
//    $table = $c->get('db')->table('table_name');
//    return new \App\Controller\HomeController($view, $logger, $table);
//};

