<?php

namespace WatchNext\WatchNext\Domain\User\Form;

use WatchNext\Engine\Request\Request;

class UserLoginForm
{
    private bool $isPost;
    public readonly string $login;
    public readonly string $password;
    public readonly bool $rememberMe;

    public function __construct(private readonly Request $request)
    {
    }

    public function isValid(): bool
    {
        $this->isPost = $this->request->isPost();

        if ($this->isPost) {
            $this->login = $this->request->post('login', '');
            $this->password = $this->request->post('password', '');
            $this->rememberMe = (bool) $this->request->post('remember-me', false);
        }

        return $this->isPost;
    }
}
