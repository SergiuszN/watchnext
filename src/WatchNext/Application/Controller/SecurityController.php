<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Template\Language;
use WatchNext\WatchNext\Domain\User\User;
use WatchNext\WatchNext\Domain\User\UserRegisterForm;
use WatchNext\WatchNext\Domain\User\UserRepository;

readonly class SecurityController {
    public function __construct(
        private Request $request,
        private UserRepository $userRepository,
        private Language $language,
    ) {
    }

    public function register(): TemplateResponse|RedirectResponse {
        $form = new UserRegisterForm($this->request);

        if ($form->isValid($this->userRepository)) {
            $password = password_hash($form->password, PASSWORD_DEFAULT);
            $user = new User($form->login, $password, ['ROLE_USER']);
            $this->userRepository->save($user);

            (new FlashBag())->add('success', $this->language->trans('security.register.success'));
            return new RedirectResponse('security_login');
        }

        return new TemplateResponse('security/register.html.twig');
    }

    public function login(): TemplateResponse|RedirectResponse {
        $form = new UserRegisterForm($this->request);



        return new TemplateResponse('security/login.html.twig');
    }
}