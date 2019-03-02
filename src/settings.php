<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
            'cache'         =>  __DIR__ . '/../logs/cache',
        ],
//        'view' => [
//            'template_path' => __DIR__ .'templates',
//            'twig' => [
//                'cache' => __DIR__ .'/../cache/twig',
//                'debug' => true,
//            ],
//        ],
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/' . date("Ymd") . '/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        //db settings
//        'db' => [
//            'host'   => "localhost",
//            'user'   => "root",
//            'pass'   => "root",
//            'dbname' => "cps_tool",
//
//        ]
        'db' => [
            'driver' => 'mysql',
            'host' => '',
            'database' => 'cps_admin',
            'username' => 'xs',
            'password' => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
        'db_stack' => [
            'driver' => 'mysql',
            'host' => '',
            'database' => 'cps_stacks',
            'username' => 'xs',
            'password' => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
    ],
];