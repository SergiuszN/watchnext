<?php

namespace WatchNext\Engine\Session;

use WatchNext\WatchNext\Domain\User\User;

class Auth {
    private static ?User $user;

    public function init(): void {
        self::$user = isset($_SESSION['main.auth.user']) ? unserialize($_SESSION['main.auth.user']) : null;
    }

    public function getUser(): ?User {
        return self::$user;
    }

    public function isAuth(): bool {
        return isset($_SESSION['main.auth.user']);
    }
}