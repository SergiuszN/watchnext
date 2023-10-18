<?php

return [
    'homepage_index' => ['GET', '/', \WatchNext\WatchNext\Application\Controller\HomepageController::class . '::' . 'index'],
    'homepage_app' => ['GET', '/app', \WatchNext\WatchNext\Application\Controller\HomepageController::class . '::' . 'app'],

    'security_register' => [['GET', 'POST'], '/register', \WatchNext\WatchNext\Application\Controller\SecurityController::class . '::' . 'register'],
    'security_login' => [['GET', 'POST'], '/login', \WatchNext\WatchNext\Application\Controller\SecurityController::class . '::' . 'login'],
    'security_logout' => ['GET', '/logout', \WatchNext\WatchNext\Application\Controller\SecurityController::class . '::' . 'logout'],
];