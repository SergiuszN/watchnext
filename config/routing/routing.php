<?php

return [
    'homepage_index' => ['GET', '/', \WatchNext\WatchNext\Application\Controller\HomepageController::class . '::' . 'index'],
    'homepage_app' => ['GET', '/app', \WatchNext\WatchNext\Application\Controller\HomepageController::class . '::' . 'app'],

    'security_register' => [['GET', 'POST'], '/register', \WatchNext\WatchNext\Application\Controller\SecurityController::class . '::' . 'register'],
    'security_login' => [['GET', 'POST'], '/login', \WatchNext\WatchNext\Application\Controller\SecurityController::class . '::' . 'login'],
    'security_logout' => ['GET', '/logout', \WatchNext\WatchNext\Application\Controller\SecurityController::class . '::' . 'logout'],
    'security_not_found' => ['GET', '/not-found', \WatchNext\WatchNext\Application\Controller\SecurityController::class . '::' . 'notFound'],
    'security_access_denied' => ['GET', '/access-denied', \WatchNext\WatchNext\Application\Controller\SecurityController::class . '::' . 'accessDenied'],

    'item_add' => [['GET', 'POST'], '/item/add', \WatchNext\WatchNext\Application\Controller\ItemController::class . '::' . 'add'],

    'catalog_show' => ['GET', '/catalog/{catalog:\d+}', \WatchNext\WatchNext\Application\Controller\CatalogController::class . '::' . 'show'],
    'catalog_show_page' => ['GET', '/catalog/{catalog:\d+}/{page:\d+}', \WatchNext\WatchNext\Application\Controller\CatalogController::class . '::' . 'show'],
    'catalog_add' => [['GET', 'POST'], '/catalog/add', \WatchNext\WatchNext\Application\Controller\CatalogController::class . '::' . 'add'],
    'catalog_remove' => ['GET', '/catalog/remove/{catalog:\d+}', \WatchNext\WatchNext\Application\Controller\CatalogController::class . '::' . 'remove'],
];