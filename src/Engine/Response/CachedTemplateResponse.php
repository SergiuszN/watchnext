<?php

namespace WatchNext\Engine\Response;

readonly class CachedTemplateResponse
{
    public function __construct(
        public string $template,
        public array $params = [],
        public ?int $ttl = null
    ) {
    }
}
