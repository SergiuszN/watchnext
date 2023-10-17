<?php

namespace WatchNext\Engine\Response;

readonly class RedirectResponse {
    public function __construct(
        public string $route,
        public array  $params = []
    ) {
    }
}