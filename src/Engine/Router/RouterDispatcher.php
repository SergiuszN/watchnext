<?php

namespace WatchNext\Engine\Router;

use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use WatchNext\Engine\Cache\CacheInterface;
use function FastRoute\cachedDispatcher;

readonly class RouterDispatcher {
    public function __construct(private CacheInterface $cache) {
    }

    /**
     * @throws Exception
     */
    public function dispatch(): DispatchedRoute {
        $routeInfo = $this->getRouteInfo();

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $_SERVER['REQUEST_ROUTE'] = 'NOT_FOUND';

                return new DispatchedRoute(RouterDispatcherStatusEnum::NOT_FOUND);
            case Dispatcher::METHOD_NOT_ALLOWED:
                $_SERVER['REQUEST_ROUTE'] = 'METHOD_NOT_ALLOWED';

                return new DispatchedRoute(RouterDispatcherStatusEnum::METHOD_NOT_ALLOWED);
            case Dispatcher::FOUND:
                [$class, $action, $routeName] = explode('::', $routeInfo[1]);
                $_SERVER['REQUEST_ROUTE'] = $routeName;

                return new DispatchedRoute(RouterDispatcherStatusEnum::FOUND, $class, $action, $routeInfo[2]);
        }

        throw new Exception('This code must not be ever runed!');
    }

    private function getRouteInfo(): array {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();

        if ($_ENV['APP_ENV'] === 'prod') {
            return $this->cache->get("router:$httpMethod:$uri", function () use ($httpMethod, $uri) {
                return $this->getDispatcher()->dispatch($httpMethod, $uri);
            });
        } else {
            return $this->getDispatcher()->dispatch($httpMethod, $uri);
        }
    }

    private function getDispatcher(): Dispatcher {
        return cachedDispatcher(function (RouteCollector $r) {
            $routes = require __DIR__ . '/../../../config/routing/routing.php';

            foreach ($routes as $name => $route) {
                $r->addRoute($route[0], $route[1], $route[2] . '::' . $name);
            }
        }, [
            'cacheFile' => __DIR__ . '/../../../var/cache/router.cache.php',
            'cacheDisabled' => $_ENV['APP_ENV'] === 'dev',
        ]);
    }

    private function getUri(): string {
        $uri = $_SERVER['REQUEST_URI'];

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        return rawurldecode($uri);
    }
}