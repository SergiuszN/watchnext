<?php

namespace WatchNext\WatchNext\Domain\User;

use WatchNext\Engine\Event\SyncEventDispatcher;
use WatchNext\WatchNext\Domain\User\Query\UserCreatedEvent;

readonly class UserCreator
{
    public function __construct(
        private UserRepository $userRepository,
        private SyncEventDispatcher $eventManager,
    ) {
    }

    public function createOrdinaryUser(string $login, string $password, string $language): void
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $user = new User($login, $password, LanguageEnum::from($language), ['ROLE_USER']);
        $this->userRepository->save($user);

        $this->eventManager->dispatch(new UserCreatedEvent($user->getId()));
    }
}
