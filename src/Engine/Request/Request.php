<?php

namespace WatchNext\Engine\Request;

use WatchNext\Engine\Router\DispatchedRoute;

class Request {
    static private ?DispatchedRoute $route = null;
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
}