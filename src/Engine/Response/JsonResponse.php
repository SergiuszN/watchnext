<?php

namespace WatchNext\Engine\Response;

readonly class JsonResponse {
    public function __construct(public array $data, public int $httpCode = 200) {
    }
}