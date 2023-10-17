<?php

namespace WatchNext\WatchNext\Domain\User;

use InvalidArgumentException;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Template\Language;
use Webmozart\Assert\Assert;

class UserRegisterForm {
    private bool $isPost;
    public readonly string $login;
    public readonly string $password;

    public function __construct(Request $request) {
        $this->isPost = $request->isPost();

        if ($this->isPost) {
            $this->login = $request->post('login', '');
            $this->password = $request->post('password', '');
        }
    }

    public function isValid(UserRepository $userRepository): bool {
        if (!$this->isPost) {
            return false;
        }

        try {
            $l = new Language();
            Assert::minLength($this->login, 3, "login:{$l->trans('user.login.assert.minLength')}");
            Assert::minLength($this->password, 8, "password:{$l->trans('user.password.assert.minLength')}");
            Assert::notEq($this->password, $this->login, "password:{$l->trans('user.password.assert.sameAsLogin')}");
            Assert::false($userRepository->doesExist($this->login), "login:{$l->trans('user.login.assert.alreadyExist')}");
        } catch (InvalidArgumentException $invalidArgumentException) {
            (new FlashBag())->addValidationErrors($invalidArgumentException);
            return false;
        }

        return true;
    }
}