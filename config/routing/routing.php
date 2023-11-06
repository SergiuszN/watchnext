<?php

use WatchNext\WatchNext\Application\Controller\CatalogController;
use WatchNext\WatchNext\Application\Controller\HomepageController;
use WatchNext\WatchNext\Application\Controller\ItemController;
use WatchNext\WatchNext\Application\Controller\SecurityController;

return [
    'homepage_index' => ['GET', '/', HomepageController::class . '::index'],
    'homepage_app' => ['GET', '/app', HomepageController::class . '::app'],

    'security_register' => [['GET', 'POST'], '/register', SecurityController::class . '::register'],
    'security_login' => [['GET', 'POST'], '/login', SecurityController::class . '::login'],
    'security_logout' => ['GET', '/logout', SecurityController::class . '::logout'],
    'security_not_found' => ['GET', '/not-found', SecurityController::class . '::notFound'],
    'security_access_denied' => ['GET', '/access-denied', SecurityController::class . '::accessDenied'],

    'item_add' => [['GET', 'POST'], '/item/add', ItemController::class . '::add'],
    'item_toggle_watched' => ['GET', '/item/toggle-watched/{item:\d+}', ItemController::class . '::toggleWatched'],
    'item_note' => ['POST', '/item/note/{item:\d+}', ItemController::class . '::note'],
    'item_delete' => ['GET', '/item/delete/{item:\d+}', ItemController::class . '::delete'],
    'item_move' => [['GET', 'POST'], '/item/move/{item:\d+}', ItemController::class . '::move'],
    'item_copy' => [['GET', 'POST'], '/item/copy/{item:\d+}', ItemController::class . '::copy'],
    'item_update_tags' => ['POST', '/item/update-tags/{item:\d+}', ItemController::class . '::updateTags'],
    'item_search' => ['GET', '/item/search', ItemController::class . '::search'],
    'item_search_page' => ['GET', '/item/search/{page:\d+}', ItemController::class . '::search'],

    'catalog_manage' => ['GET', '/catalog/manage', CatalogController::class . '::manage'],
    'catalog_add' => [['GET', 'POST'], '/catalog/add', CatalogController::class . '::add'],
    'catalog_remove' => ['GET', '/catalog/remove/{catalog:\d+}', CatalogController::class . '::remove'],
    'catalog_edit' => [['GET', 'POST'], '/catalog/edit/{catalog:\d+}', CatalogController::class . '::edit'],
    'catalog_set_default' => ['GET', '/catalog/set-default/{catalog:\d+}', CatalogController::class . '::setDefault'],
    'catalog_share' => ['POST', '/catalog/share/{catalog:\d+}', CatalogController::class . '::share'],
    'catalog_un_share' => ['GET', '/catalog/un-share/{catalog:\d+}/{user:\d+}', CatalogController::class . '::unShare'],
    'catalog_unsubscribe' => ['GET', '/catalog/unsubscribe/{catalog:\d+}', CatalogController::class . '::unsubscribe'],
    'catalog_show' => ['GET', '/catalog/{catalog:\d+}', CatalogController::class . '::show'],
    'catalog_show_page' => ['GET', '/catalog/{catalog:\d+}/{page:\d+}', CatalogController::class . '::show'],
];
