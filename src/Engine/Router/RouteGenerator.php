<?php

namespace WatchNext\Engine\Router;

use WatchNext\Engine\Config;

class RouteGenerator {
    private static ?array $routes = null;

    /** @noinspection PhpUnhandledExceptionInspection */
    public function make(string $name, array $params = [], bool $absolute = false): string {
        if (self::$routes === null) {
            self::$routes = (new Config())->get('routing/routing.php');
        }

        if (!isset(self::$routes[$name])) {
            throw new \Exception("Route $name is not exist!");
        }

        $route = self::$routes[$name][1];

        // Set params required in route --------------------------------------------------------------------------------

        preg_match_all('/{(\w+):?[^}]*}/', $route, $routeParams);

        foreach ($routeParams[1] as $key => $routeParam) {
            if (!isset($params[$routeParam])) {
                throw new \Exception("Route $name require parameter $routeParam!");
            }

            $route = str_replace($routeParams[0][$key], urlencode($params[$routeParam]), $route);
            unset($params[$routeParam]);
        }

        // Add custom query params -------------------------------------------------------------------------------------

        if (!empty($params)) {
            $route .= '?';
            $queryParams = [];
            foreach ($params as $key => $param) {
                $queryParams[] = $key . '=' . urlencode($param);
            }
            $route .= implode('&', $queryParams);
        }

        return !$absolute ? $route : $_ENV['SITE_URL'] . $route;
    }
}