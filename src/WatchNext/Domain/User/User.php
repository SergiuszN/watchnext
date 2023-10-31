<?php

namespace WatchNext\WatchNext\Domain\User;

use DateTimeImmutable;

class User
{
    private ?int $id = null;
    private string $login;
    private string $password;
    private DateTimeImmutable $createdAt;
    private ?string $rememberMeKey = null;
    private ?string $rememberMeToken = null;
    private array $roles;

    public function __construct(
        string $login,
        string $password,
        array $roles
    ) {
        $this->login = $login;
        $this->password = $password;
        $this->roles = $roles;
        $this->createdAt = new DateTimeImmutable();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRememberMeToken(): ?string
    {
        return $this->rememberMeToken;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function rememberMe(string $key, string $token): void
    {
        $this->rememberMeKey = $key;
        $this->rememberMeToken = $token;
    }

    public static function fromDatabase(array $user): User
    {
        $model = new User($user['login'], $user['password'], $user['roles'] ? json_decode($user['roles']) : []);
        $model->id = (int) $user['id'];
        $model->createdAt = new DateTimeImmutable($user['created_at']);
        $model->rememberMeKey = $user['remember_me_key'];
        $model->rememberMeToken = $user['remember_me_token'];

        return $model;
    }

    public function toDatabase(): array
    {
        return [
            'login' => $this->login,
            'password' => $this->password,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'remember_me_key' => $this->rememberMeKey,
            'remember_me_token' => $this->rememberMeToken,
            'roles' => json_encode($this->roles),
        ];
    }
}
