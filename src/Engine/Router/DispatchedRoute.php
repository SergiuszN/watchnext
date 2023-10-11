<?php

namespace WatchNext\Engine\Router;

readonly class DispatchedRoute {
    public RouterDispatcherStatusEnum $status;
    public ?string $class;
    public ?string $action;
    public array $vars;

    public function __construct(
        RouterDispatcherStatusEnum $status,
        string                     $class = null,
        string                     $action = null,
        array                      $vars = [],
    ) {
        $this->status = $status;
        $this->class = $class;
        $this->action = $action;
        $this->vars = $vars;
    }
}