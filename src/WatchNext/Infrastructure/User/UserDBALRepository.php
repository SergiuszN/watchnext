<?php

namespace WatchNext\WatchNext\Infrastructure\User;

use Doctrine\DBAL\Connection;
use WatchNext\Engine\Database\Database;
use WatchNext\WatchNext\Domain\User\User;
use WatchNext\WatchNext\Domain\User\UserRepository;

class UserDBALRepository implements UserRepository {
    private Connection $connection;

    public function __construct(Database $database) {
        $this->connection = $database->getConnection();
    }

    public function save(User $user): void {
        if ($user->getId() === null) {
             $this->connection->prepare("
                INSERT INTO `user`(
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
            ")->executeStatement($user->toDatabase());
            $user->setId($this->connection->lastInsertId());
        } else {
            $this->connection->prepare("
                UPDATE `user` SET
                    `login` = :login,
                    `password` = :password,
                    `created_at` = :created_at,
                    `remember_me_key` = :remember_me_key,
                    `remember_me_token` = :remember_me_token,
                    `roles` = :roles
                WHERE `id` = :id
            ")->executeStatement(array_merge($user->toDatabase(), ['id' => $user->getId()]));
        }
    }

    public function find(int $id): ?User {
        $data = $this->connection->prepare("SELECT * FROM `user` WHERE id=:id")
            ->executeQuery(['id' => $id])
            ->fetchAssociative();

        return $data ? User::fromDatabase($data) : null;
    }

    public function doesExist(string $login): bool {
        return $this->connection->prepare("
            SELECT COUNT(`id`) FROM `user` WHERE `login` = :login
        ")
            ->executeQuery(['login' => $login])
            ->fetchOne() > 0;
    }
}