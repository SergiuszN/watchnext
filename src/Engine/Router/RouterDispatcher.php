<?php

namespace WatchNext\Engine\Router;

use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\cachedDispatcher;

class RouterDispatcher {
    /**
     * @throws Exception
     */
    public function dispatch(): DispatchedRoute {
        $dispatcher = cachedDispatcher(function (RouteCollector $r) {
            $routes = require __DIR__ . '/../../../config/routing/routing.php';

            foreach ($routes as $route) {
                $r->addRoute(...$route);
            }
        }, [
            'cacheFile' => __DIR__ . '/../../../var/cache/router.cache.php',
            'cacheDisabled' => $_ENV['APP_ENV'] === 'dev',
        ]);

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return new DispatchedRoute(RouterDispatcherStatusEnum::NOT_FOUND);
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new DispatchedRoute(RouterDispatcherStatusEnum::METHOD_NOT_ALLOWED);
            case Dispatcher::FOUND:
                [$class, $action] = explode('::', $routeInfo[1]);
                return new DispatchedRoute(RouterDispatcherStatusEnum::FOUND, $class, $action, $routeInfo[2]);
        }

        throw new Exception('This code must not be ever runed!');
    }
}