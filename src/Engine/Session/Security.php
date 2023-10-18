<?php

namespace WatchNext\Engine\Session;

use WatchNext\WatchNext\Domain\User\User;
use WatchNext\WatchNext\Domain\User\UserRepository;

readonly class Security {
    public function __construct(private UserRepository $userRepository) {
    }

    public function init(): void {
        session_start();

        $_SESSION[CSFR::TOKEN_KEY] = $_SESSION[CSFR::TOKEN_KEY] ?? bin2hex(random_bytes(20));

        $this->tryAuthorizeFromCookie();

        (new Auth())->init();
    }

    public function authorize(User $user, bool $rememberMe = false): void {
        $_SESSION['main.auth.user'] = serialize($user);

        if ($rememberMe) {
            $key = bin2hex(random_bytes(8));
            $token = bin2hex(random_bytes(30));
            $hash = password_hash($token, PASSWORD_DEFAULT);

            setcookie('rmmbr.key', $key);
            setcookie('rmmbr.token', $token);

            $user->rememberMe($key, $hash);
            $this->userRepository->save($user);
        }
    }

    public function unathorize(): void {
        unset($_SESSION['main.auth.user']);
        setcookie('rmmbr.key', '', -1);
        setcookie('rmmbr.token', '', -1);
    }

    public function tryAuthorizeFromCookie(): void {
        if (isset($_SESSION['main.auth.user']) || !isset($_COOKIE['rmmbr.key'])) {
            return;
        }

        $user = $this->userRepository->findByRememberMeKey($_COOKIE['rmmbr.key']);

        if (!$user) {
            return;
        }

        if (password_verify($_COOKIE['rmmbr.token'] ?? null, $user->getRememberMeToken())) {
            $this->authorize($user);
        }
    }
}