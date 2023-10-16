<?php

namespace WatchNext\Engine\Session;

class CSFR {
    public const TOKEN_KEY = 'security.csfr';

    public function validate(string $token): bool {
        return $token === $_SESSION[self::TOKEN_KEY];
    }

    public function get(): string {
        return $_SESSION[self::TOKEN_KEY];
    }
}