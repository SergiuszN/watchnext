<?php

return [
    'home_index' => ['GET', '/', \WatchNext\WatchNext\Application\Controller\HomepageController::class . '::' . 'index'],
    'home_about_us' => ['GET', '/about-us', \WatchNext\WatchNext\Application\Controller\HomepageController::class . '::' . 'index'],
    'home_toc' => ['GET', '/toc', \WatchNext\WatchNext\Application\Controller\HomepageController::class . '::' . 'index'],

    'security_register' => [['GET', 'POST'], '/register', \WatchNext\WatchNext\Application\Controller\SecurityController::class . '::' . 'register'],
    'security_login' => [['GET', 'POST'], '/login', \WatchNext\WatchNext\Application\Controller\SecurityController::class . '::' . 'login'],
];