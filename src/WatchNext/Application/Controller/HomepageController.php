<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Session\Auth;
use WatchNext\Engine\Session\SecurityFirewall;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;

class HomepageController {
    public function __construct(
        private SecurityFirewall $firewall,
        private CatalogRepository $catalogRepository,
        private Auth $auth,
    ) {
    }

    public function index(): TemplateResponse {
        return new TemplateResponse('page/homepage/index.html.twig');
    }

    /**
     * @throws AccessDeniedException
     */
    public function app(): RedirectResponse {
        $this->firewall->throwIfNotGranted('ROLE_HOMEPAGE_APP');
        $defaultCatalog = $this->catalogRepository->findDefaultForUser($this->auth->getUserId());
        return new RedirectResponse('catalog_show', ['catalog' => $defaultCatalog->getId()]);
    }
}