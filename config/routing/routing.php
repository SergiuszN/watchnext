<?php

use WatchNext\WatchNext\Application\Controller\CatalogController;
use WatchNext\WatchNext\Application\Controller\HomepageController;
use WatchNext\WatchNext\Application\Controller\ItemController;
use WatchNext\WatchNext\Application\Controller\SecurityController;

return [
    'homepage_index' => ['GET', '/', HomepageController::class . '::' . 'index'],
    'homepage_app' => ['GET', '/app', HomepageController::class . '::' . 'app'],

    'security_register' => [['GET', 'POST'], '/register', SecurityController::class . '::' . 'register'],
    'security_login' => [['GET', 'POST'], '/login', SecurityController::class . '::' . 'login'],
    'security_logout' => ['GET', '/logout', SecurityController::class . '::' . 'logout'],
    'security_not_found' => ['GET', '/not-found', SecurityController::class . '::' . 'notFound'],
    'security_access_denied' => ['GET', '/access-denied', SecurityController::class . '::' . 'accessDenied'],

    'item_add' => [['GET', 'POST'], '/item/add', ItemController::class . '::' . 'add'],

    'catalog_manage' => ['GET', '/catalog/manage', CatalogController::class . '::' . 'manage'],
    'catalog_add' => [['GET', 'POST'], '/catalog/add', CatalogController::class . '::' . 'add'],
    'catalog_remove' => ['GET', '/catalog/remove/{catalog:\d+}', CatalogController::class . '::' . 'remove'],
    'catalog_edit' => [['GET', 'POST'], '/catalog/edit/{catalog:\d+}', CatalogController::class . '::' . 'edit'],
    'catalog_set_default' => ['GET', '/catalog/set-default/{catalog:\d+}', CatalogController::class . '::' . 'setDefault'],
    'catalog_show' => ['GET', '/catalog/{catalog:\d+}', CatalogController::class . '::' . 'show'],
    'catalog_show_page' => ['GET', '/catalog/{catalog:\d+}/{page:\d+}', CatalogController::class . '::' . 'show'],
];