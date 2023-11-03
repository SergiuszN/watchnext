<?php

namespace WatchNext\WatchNext\Domain\User\Form;

use InvalidArgumentException;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Template\Translator;
use WatchNext\WatchNext\Domain\User\UserRepository;
use Webmozart\Assert\Assert;

class UserRegisterForm
{
    private bool $isPost;
    public readonly string $login;
    public readonly string $password;
    public readonly string $repeatPassword;

    public function __construct(private readonly Request $request, private readonly Translator $t)
    {
        $this->isPost = $this->request->isPost();
    }

    public function load(): self
    {
        if ($this->isPost) {
            $this->login = $this->request->post('login', '');
            $this->password = $this->request->post('password', '');
            $this->repeatPassword = $this->request->post('password-repeat', '');
        }

        return $this;
    }

    public function isValid(UserRepository $userRepository): bool
    {
        if (!$this->isPost) {
            return false;
        }

        try {
            Assert::minLength($this->login, 3, "login:{$this->t->trans('user.login.assert.minLength')}");
            Assert::minLength($this->password, 8, "password:{$this->t->trans('user.password.assert.minLength')}");
            Assert::notEq($this->password, $this->login, "password:{$this->t->trans('user.password.assert.sameAsLogin')}");
            Assert::eq($this->password, $this->repeatPassword, "password:{$this->t->trans('user.password.assert.areNotTheSame')}");
            Assert::false($userRepository->doesExist($this->login), "login:{$this->t->trans('user.login.assert.alreadyExist')}");
        } catch (InvalidArgumentException $invalidArgumentException) {
            (new FlashBag())->addValidationErrors($invalidArgumentException);

            return false;
        }

        return true;
    }
}
