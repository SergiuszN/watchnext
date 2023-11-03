<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Event\EventManager;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Session\Security;
use WatchNext\Engine\Template\Translator;
use WatchNext\WatchNext\Domain\User\Form\UserLoginForm;
use WatchNext\WatchNext\Domain\User\Form\UserRegisterForm;
use WatchNext\WatchNext\Domain\User\Query\UserCreatedQuery;
use WatchNext\WatchNext\Domain\User\User;
use WatchNext\WatchNext\Domain\User\UserRepository;

readonly class SecurityController
{
    public function __construct(
        private Request $request,
        private UserRepository $userRepository,
        private Translator $language,
        private Security $security,
        private FlashBag $flashBag,
        private EventManager $eventManager,
        private UserRegisterForm $userRegisterForm,
    ) {
    }

    public function register(): TemplateResponse|RedirectResponse
    {
        $form = $this->userRegisterForm->load();

        if ($form->isValid($this->userRepository)) {
            $password = password_hash($form->password, PASSWORD_DEFAULT);
            $user = new User($form->login, $password, ['ROLE_USER']);
            $this->userRepository->save($user);

            $this->eventManager->dispatch(new UserCreatedQuery($user->getId()));

            $this->flashBag->add('success', $this->language->trans('security.register.success'));

            return new RedirectResponse('security_login');
        }

        return new TemplateResponse('page/security/register.html.twig');
    }

    public function login(): TemplateResponse|RedirectResponse
    {
        $form = new UserLoginForm($this->request);

        if ($this->security->getUserId()) {
            return new RedirectResponse('homepage_app');
        }

        if ($form->isValid()) {
            $user = $this->userRepository->findByLogin($form->login);

            if (!$user) {
                $this->flashBag->add('error', $this->language->trans('security.login.wrongUserOrPassword'));

                return new RedirectResponse('security_login');
            }

            if (!password_verify($form->password, $user->getPassword())) {
                $this->flashBag->add('error', $this->language->trans('security.login.wrongUserOrPassword'));

                return new RedirectResponse('security_login');
            }

            $this->security->authorize($user, $form->rememberMe);

            return new RedirectResponse('homepage_app');
        }

        return new TemplateResponse('page/security/login.html.twig');
    }

    public function logout(): RedirectResponse
    {
        $this->security->unauthorize();

        return new RedirectResponse('security_login');
    }

    public function accessDenied(): RedirectResponse
    {
        return new RedirectResponse('security_login');
    }

    public function notFound(): TemplateResponse
    {
        return new TemplateResponse('page/security/not_found.html.twig');
    }
}
