<?php

namespace WatchNext\WatchNext\Domain\User;

interface UserRepository
{
    public function save(User $user): void;

    public function find(int $id): ?User;

    public function doesExist(string $login): bool;

    public function findByLogin(string $login): ?User;

    public function findByRememberMeKey(string $rememberMeKey): ?User;

    /** @return User[] */
    public function findSharedWithUsersForCatalog(int $catalogId): array;
}
