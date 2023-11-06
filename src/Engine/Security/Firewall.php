<?php

namespace WatchNext\Engine\Security;

use WatchNext\Engine\Cache\ApcuCache;
use WatchNext\Engine\Config;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\WatchNext\Domain\User\User;

class Firewall
{
    private static ?array $roleTree = null;
    private static ?array $access = null;
    private static ?array $loggedUserRoles = null;

    public function __construct(
        private readonly Config $config,
        private readonly ApcuCache $cache
    ) {
    }

    public function buildTree(?User $user): void
    {
        if (self::$roleTree !== null) {
            return;
        }

        if (!$user) {
            self::$roleTree = [];

            return;
        }

        self::$loggedUserRoles = $user->getRoles();

        $config = $this->config->get('security.php');

        self::$roleTree = ENV === 'prod'
            ? $this->cache->get('security.firewall.tree', fn () => $this->buildRoleTree($config['roles']))
            : $this->buildRoleTree($config['roles']);

        self::$access = $config['access_control'];
    }

    public function isGranted(string $role, User $user = null): bool
    {
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

    public function throwIfNotGranted(string $role, User $user = null): void
    {
        if (!$this->isGranted($role, $user)) {
            throw new AccessDeniedException();
        }
    }

    public function throwIfPathNotAccessible(string $uri): void
    {
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

    private function buildRoleTree(array $roles): array
    {
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

    private function mapFlatRoles(array &$roles, array $flatRoles, array $parents): void
    {
        foreach ($parents as $parent) {
            $roles[] = $parent;

            if (isset($flatRoles[$parent])) {
                $this->mapFlatRoles($roles, $flatRoles, $flatRoles[$parent]);
            }
        }
    }
}
