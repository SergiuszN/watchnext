<?php

namespace WatchNext\Engine\Session;

readonly class Auth {
    public function __construct() {
    }

    public function getUser(): mixed {
        return isset($_SESSION['main.auth.user']) ? unserialize($_SESSION['main.auth.user']) : null;
    }

    public function update($user): void {
        $_SESSION['main.auth.user'] = serialize($user);
    }

    public function unathorize(): void {
        unset($_SESSION['main.auth.user']);
    }

    public function isAuth(): bool {
        return isset($_SESSION['main.auth.user']);
    }
}