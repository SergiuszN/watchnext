<?php

namespace WatchNext\Engine\Security;

use Exception;
use WatchNext\WatchNext\Domain\User\User;
use WatchNext\WatchNext\Domain\User\UserRepository;

readonly class Auth
{
    public const AUTH_SESSION_KEY = 'main.auth.user';

    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * @throws Exception
     */
    public function authorize(User $user, bool $rememberMe = false): void
    {
        $_SESSION[Auth::AUTH_SESSION_KEY] = serialize($user);

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

    public function unauthorize(): void
    {
        unset($_SESSION[Auth::AUTH_SESSION_KEY]);
        setcookie('rmmbr.key', '', -1);
        setcookie('rmmbr.token', '', -1);
    }

    /**
     * @throws Exception
     */
    public function tryAuthorizeFromCookie(): void
    {
        if (isset($_SESSION[Auth::AUTH_SESSION_KEY]) || !isset($_COOKIE['rmmbr.key'])) {
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

    public function refresh(): void
    {
        $user = unserialize($_SESSION[Auth::AUTH_SESSION_KEY]);
        $user = $this->userRepository->find($user->getId());
        $_SESSION[Auth::AUTH_SESSION_KEY] = serialize($user);
    }
}
