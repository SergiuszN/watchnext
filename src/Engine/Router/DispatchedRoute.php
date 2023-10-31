<?php

namespace WatchNext\Engine\Router;

readonly class DispatchedRoute {

    public function __construct(
        public RouterDispatcherStatusEnum $status,
        public ?string                    $routeName = null,
        public ?string                    $class = null,
        public ?string                    $action = null,
        public array                      $vars = [],
    ) {
    }
}