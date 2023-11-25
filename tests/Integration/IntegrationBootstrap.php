<?php

namespace Integration;

use WatchNext\Engine\Container;
use WatchNext\WatchNext\Domain\User\UserCreator;
use WatchNext\WatchNext\Domain\User\UserRepository;

readonly class IntegrationBootstrap
{
    private UserRepository $userRepository;
    private UserCreator $userCreator;

    public function __construct(
        private Container $container,
    ) {
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->userCreator = $this->container->get(UserCreator::class);
    }

    public function __invoke(): void
    {
        if ($this->userRepository->doesExist('test1')) {
            $this->userRepository->remove($this->userRepository->findByLogin('test1'));
        }

        if ($this->userRepository->doesExist('test2')) {
            $this->userRepository->remove($this->userRepository->findByLogin('test2'));
        }

        $this->userCreator->createOrdinaryUser('test1', 'test1', 'en');
        $this->userCreator->createOrdinaryUser('test2', 'test2', 'en');
    }
}
