<?php

namespace WatchNext\Engine\Request;

use WatchNext\Engine\Router\DispatchedRoute;

class Request {
    static private ?DispatchedRoute $route = null;
    static private array $params = [];
    private array $server;
    private array $get;
    private array $post;

    public function __construct() {
        $this->server = $_SERVER;
        $this->get = $_GET;
        $this->post = $_POST;
    }

    public function setRoute(DispatchedRoute $route): void {
        self::$route = $route;
    }

    public function setParams(array $params): void {
        self::$params = $params;
    }

    public function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isPost(): bool {
        return $this->getMethod() === 'POST';
    }

    public function get(string $name, mixed $defaultValue = null): mixed {
        return $this->get[$name] ?? $defaultValue;
    }

    public function post(string $name, mixed $defaultValue = null): mixed {
        return $this->post[$name] ?? $defaultValue;
    }

    public function hasPost(string $name): bool {
        return isset($this->post[$name]);
    }

    public function getRoute(): ?DispatchedRoute {
        return self::$route;
    }

    public function getParam(string $name, mixed $defaultValue = null): mixed {
        return self::$params[$name] ?? $defaultValue;
    }
}