<?php

namespace WatchNext\Engine\Response;

readonly class TemplateResponse {
    public function __construct(
        public string $template,
        public array $params = []
    ) {
    }
}