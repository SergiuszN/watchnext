<?php

namespace WatchNext\Engine\Test;

use Exception;
use WatchNext\Engine\Dispatcher\HttpDispatcher;
use WatchNext\Engine\Security\Security;
use WatchNext\WatchNext\Domain\User\UserRepository;

class Client
{
    private string $content;
    private int|bool $code;

    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly HttpDispatcher $dispatcher
    ) {
        $this->security->init();
    }

    public function logout(): void
    {
        $this->security->unauthorize();
    }

    public function login(string $login): void
    {
        $this->security->authorize($this->userRepository->findByLogin($login));
    }

    public function get(string $url): self
    {
        $this->request('GET', $url);

        return $this;
    }

    public function post(string $url, array $data = []): self
    {
        $this->request('POST', $url, $data);

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function contains(string $string): bool
    {
        return str_contains($this->content, $string);
    }

    public function isCode(int $code): bool
    {
        return $this->code === $code;
    }

    public function getCode(): int
    {
        return is_int($this->code) ? $this->code : 0;
    }

    public function request(string $method, string $url, array $data = []): void
    {
        $this->content = '';
        $this->code = false;

        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $url;
        $_POST = $data;

        ob_start();

        try {
            $this->dispatcher->dispatch();
        } catch (Exception $e) {
            $this->content = ob_get_clean();
            throw $e;
        }

        $this->content = ob_get_clean();
        $this->code = http_response_code();
    }
}
