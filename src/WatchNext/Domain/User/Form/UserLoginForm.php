<?php

namespace WatchNext\WatchNext\Domain\User\Form;

use WatchNext\Engine\Request\Request;

class UserLoginForm
{
    private bool $isPost;
    public readonly string $login;
    public readonly string $password;
    public readonly bool $rememberMe;

    public function __construct(Request $request)
    {
        $this->isPost = $request->isPost();

        if ($this->isPost) {
            $this->login = $request->post('login', '');
            $this->password = $request->post('password', '');
            $this->rememberMe = (bool) $request->post('remember-me', false);
        }
    }

    public function isValid(): bool
    {
        return $this->isPost;
    }
}
