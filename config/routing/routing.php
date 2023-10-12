<?php

return [
    'home_index' => ['GET', '/', \WatchNext\App\Application\Controller\HomepageController::class . '::' . 'index'],
    'home_test' => ['GET', '/test', \WatchNext\App\Application\Controller\HomepageController::class . '::' . 'test'],
    'home_articles_default' => ['GET', '/articles/{id:\d+}', \WatchNext\App\Application\Controller\HomepageController::class . '::' . 'index'],
    'home_articles' => ['GET', '/articles/{id:\d+}/{title}', \WatchNext\App\Application\Controller\HomepageController::class . '::' . 'index'],
    'home_post' => ['GET', '/post/{id:\d+}/{title}', \WatchNext\App\Application\Controller\HomepageController::class . '::' . 'index'],
];