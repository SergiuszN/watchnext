<?php

namespace WatchNext\Engine\Security;

use Exception;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\WatchNext\Domain\User\User;

class Security
{
    private static ?User $user = null;
    private static ?int $userId = null;

    private static bool $inited = false;

    public function __construct(
        readonly private Auth $auth,
        readonly private Firewall $firewall,
    ) {
    }

    public function init(): void
    {
        if (!self::$inited) {
            session_start();
        }

        $_SESSION[CSFR::TOKEN_KEY] = $_SESSION[CSFR::TOKEN_KEY] ?? bin2hex(random_bytes(20));

        if (isset($_SESSION[Auth::AUTH_SESSION_KEY])) {
            $this->auth->refresh();
        } else {
            $this->tryAuthorizeFromCookie();
        }

        self::$user = isset($_SESSION[Auth::AUTH_SESSION_KEY]) ? unserialize($_SESSION[Auth::AUTH_SESSION_KEY]) : null;
        self::$userId = self::$user?->getId();

        $this->firewall->buildTree(self::$user);
        self::$inited = true;
    }

    public function getUser(): ?User
    {
        return self::$user;
    }

    public function getUserId(): ?int
    {
        return self::$userId;
    }

    public function isAuth(): bool
    {
        return self::$userId !== null;
    }

    public function isGranted(string $role, User $user = null): bool
    {
        return $this->firewall->isGranted($role, $user);
    }

    /**
     * @throws AccessDeniedException
     */
    public function throwIfNotGranted(string $role, User $user = null): void
    {
        $this->firewall->throwIfNotGranted($role, $user);
    }

    /**
     * @throws AccessDeniedException
     */
    public function throwIfPathNotAccessible(string $uri): void
    {
        $this->firewall->throwIfPathNotAccessible($uri);
    }

    /**
     * @throws Exception
     */
    public function authorize(User $user, bool $rememberMe = false): void
    {
        $this->auth->authorize($user, $rememberMe);
    }

    public function unauthorize(): void
    {
        $this->auth->unauthorize();
    }

    /**
     * @throws Exception
     */
    public function tryAuthorizeFromCookie(): void
    {
        $this->auth->tryAuthorizeFromCookie();
    }
}
