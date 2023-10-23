<?php

namespace WatchNext\Engine\Session;

use WatchNext\Engine\Cache\CacheInterface;
use WatchNext\Engine\Config;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\WatchNext\Domain\User\User;

class SecurityFirewall {
    private static ?array $roleTree = null;
    private static ?array $access = null;
    private static ?array $loggedUserRoles = null;

    public function __construct(Auth $auth, Config $config, CacheInterface $cache) {
        if (self::$roleTree !== null) {
            return;
        }

        $user = $auth->getUser();

        if (!$user) {
            self::$roleTree = [];
            return;
        }

        self::$loggedUserRoles = $user->getRoles();

        self::$roleTree = $_ENV['APP_ENV'] === 'prod'
            ? $cache->get('security.firewall.tree', fn() => $this->buildRoleTree($config->get('security.php')['roles']))
            : $this->buildRoleTree($config->get('security.php')['roles']);

        self::$access = $config->get('security.php')['access_control'];
    }

    public function isGranted(string $role, ?User $user = null): bool {
        $userRoles = $user ? $user->getRoles() : self::$loggedUserRoles;

        if (!is_array($userRoles)) {
            return false;
        }

        if (in_array($role, $userRoles)) {
            return true;
        }

        if (isset(self::$roleTree[$role])) {
            return !empty(array_intersect($userRoles, self::$roleTree[$role]));
        }

        return false;
    }

    public function throwIfNotGranted(string $role, ?User $user = null): void {
        if (!$this->isGranted($role, $user)) {
            throw new AccessDeniedException();
        }
    }

    public function throwIfPathNotAccessible(string $uri): void {
        if (self::$access === null) {
            return;
        }

        foreach (self::$access as $pattern => $roles) {
            if (str_starts_with($uri, $pattern)) {
                foreach ($roles as $role) {
                    if ($this->isGranted($role)) {
                        continue 2;
                    }
                }

                throw new AccessDeniedException();
            }
        }
    }

    private function buildRoleTree(array $roles): array {
        $flatRoles = [];

        foreach ($roles as $role => $subRoles) {
            foreach ($subRoles as $subRole) {
                if (!isset($flatRoles[$subRole])) {
                    $flatRoles[$subRole] = [];
                }

                $flatRoles[$subRole][] = $role;
            }
        }

        $flatRoles = array_map('array_unique', $flatRoles);

        $tree = [];
        foreach ($flatRoles as $role => $parents) {
            $tree[$role] = [];
            $this->mapFlatRoles($tree[$role], $flatRoles, $parents);
        }

        return $tree;
    }

    private function mapFlatRoles(array &$roles, array $flatRoles, array $parents): void {
        foreach ($parents as $parent) {
            $roles[] = $parent;

            if (isset($flatRoles[$parent])) {
                $this->mapFlatRoles($roles, $flatRoles, $flatRoles[$parent]);
            }
        }
    }
}