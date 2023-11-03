<?php

namespace WatchNext\Engine\Response;

readonly class RedirectRefererResponse
{
    public string $uri;

    public function __construct()
    {
        $this->uri = $_SERVER['HTTP_REFERER'];
    }
}
