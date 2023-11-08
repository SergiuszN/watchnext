<?php

namespace WatchNext\Engine\Router;

use Exception;
use WatchNext\Engine\Config;

class RouteGenerator
{
    private readonly ?array $routes;

    private array $cache = [];

    public function __construct(
        private Config $config
    ) {
        $this->routes = $this->config->get('routing/routing.php');
    }

    public function make(string $name, array $params = [], bool $absolute = false): string
    {
        $key = $name . '.' . implode('.', array_keys($params)) . '.' . (int) $absolute;

        if (isset($this->cache[$key])) {
            $route = $this->cache[$key];
        } else {
            $route = $this->_make($name, $params, $absolute);
            $this->cache[$key] = $route;
        }

        foreach ($params as $key => $value) {
            $route = str_replace("%$key%", $value, $route);
        }

        return $route;
    }

    /**
     * @throws Exception
     */
    private function _make(string $name, array $params = [], bool $absolute = false): string
    {
        if (!isset($this->routes[$name])) {
            throw new Exception("Route $name is not exist!");
        }

        $route = $this->routes[$name][1];

        // Set params required in route --------------------------------------------------------------------------------

        preg_match_all('/{(\w+):?[^}]*}/', $route, $routeParams);

        foreach ($routeParams[1] as $key => $routeParam) {
            if (!isset($params[$routeParam])) {
                throw new Exception("Route $name require parameter $routeParam!");
            }

            $route = str_replace($routeParams[0][$key], "%{$routeParam}%", $route);
            unset($params[$routeParam]);
        }

        // Add custom query params -------------------------------------------------------------------------------------

        if (!empty($params)) {
            $route .= '?';
            $queryParams = [];
            foreach ($params as $key => $param) {
                $queryParams[] = $key . '=' . "%$key%";
            }
            $route .= implode('&', $queryParams);
        }

        return !$absolute ? $route : $_ENV['SITE_URL'] . $route;
    }

    /**
     * @throws Exception
     */
    public function makePage(int $page): string
    {
        $route = str_contains($_SERVER['REQUEST_ROUTE'], '_page') ? $_SERVER['REQUEST_ROUTE'] : $_SERVER['REQUEST_ROUTE'] . '_page';
        $params = array_merge($_SERVER['REQUEST_ROUTE_PARAMS'], $_GET);
        $params['page'] = $page;

        return $this->make($route, $params);
    }

    public function startsFrom(string $patch): bool
    {
        return str_starts_with($_SERVER['REQUEST_ROUTE'], $patch);
    }
}
