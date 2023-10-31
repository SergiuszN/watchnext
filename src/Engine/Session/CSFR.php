<?php

namespace WatchNext\Engine\Session;

use WatchNext\Engine\Router\AccessDeniedException;

class CSFR
{
    public const TOKEN_KEY = 'security.csfr';

    public function validate(string $token): bool
    {
        return $token === $_SESSION[self::TOKEN_KEY];
    }

    /**
     * @throws AccessDeniedException
     */
    public function throwIfNotValid(string $token): void
    {
        if (!$this->validate($token)) {
            throw new AccessDeniedException();
        }
    }

    public function get(): string
    {
        return $_SESSION[self::TOKEN_KEY] ?? '';
    }
}
