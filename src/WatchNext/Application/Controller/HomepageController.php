<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Security\Security;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;

readonly class HomepageController
{
    public function __construct(
        private CatalogRepository $catalogRepository,
        private Security $security,
    ) {
    }

    public function index(): TemplateResponse
    {
        return new TemplateResponse('page/homepage/index.html.twig');
    }

    /**
     * @throws AccessDeniedException
     */
    public function app(): RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_HOMEPAGE_APP');
        $defaultCatalog = $this->catalogRepository->findDefaultForUser($this->security->getUserId());

        return new RedirectResponse('catalog_show', ['catalog' => $defaultCatalog->getId()]);
    }
}
