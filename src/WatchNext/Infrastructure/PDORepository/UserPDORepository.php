<?php

namespace WatchNext\WatchNext\Infrastructure\PDORepository;

use WatchNext\WatchNext\Domain\User\User;
use WatchNext\WatchNext\Domain\User\UserRepository;

class UserPDORepository extends PDORepository implements UserRepository {
    public function save(User $user): void {
        if ($user->getId() === null) {
            $this->database->prepare("
                INSERT INTO `user` (
                    `login`, 
                    `password`,
                    `created_at`,
                    `remember_me_key`,
                    `remember_me_token`, 
                    `roles`
                )
                VALUES (
                    :login, 
                    :password, 
                    :created_at, 
                    :remember_me_key, 
                    :remember_me_token, 
                    :roles
                )
            ")->execute($user->toDatabase());
            $user->setId($this->database->getLastInsertId());
        } else {
            $this->database->prepare("
                UPDATE `user` SET
                    `login` = :login,
                    `password` = :password,
                    `created_at` = :created_at,
                    `remember_me_key` = :remember_me_key,
                    `remember_me_token` = :remember_me_token,
                    `roles` = :roles
                WHERE `id` = :id
            ")->execute(array_merge($user->toDatabase(), ['id' => $user->getId()]));
        }
    }

    public function find(int $id): ?User {
        $data = $this->database->prepare("SELECT * FROM `user` WHERE id=:id")
            ->execute(['id' => $id])
            ->fetch();

        return $data ? User::fromDatabase($data) : null;
    }

    public function doesExist(string $login): bool {
        return $this->database->prepare("
            SELECT COUNT(`id`) FROM `user` WHERE `login` = :login LIMIT 1
        ")
            ->execute(['login' => $login])
            ->fetchSingle() > 0;
    }

    public function findByLogin(string $login): ?User {
        $data = $this->database->prepare("
            SELECT * FROM `user` WHERE `login` = :login LIMIT 1
        ")
            ->execute(['login' => $login])
            ->fetch();

        return $data ? User::fromDatabase($data) : null;
    }

    public function findByRememberMeKey(string $rememberMeKey): ?User {
        $data = $this->database->prepare("
            SELECT * FROM `user` WHERE `remember_me_key` = :remember_me_key LIMIT 1
        ")
            ->execute(['remember_me_key' => $rememberMeKey])
            ->fetch();

        return $data ? User::fromDatabase($data) : null;
    }
}