<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Session\SecurityFirewall;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;
use WatchNext\WatchNext\Domain\Catalog\CatalogVoter;
use WatchNext\WatchNext\Domain\Item\ItemRepository;

readonly class CatalogController {
    public function __construct(
        private Request $request,
        private CatalogRepository $catalogRepository,
        private SecurityFirewall $firewall,
        private CatalogVoter $catalogVoter,
        private ItemRepository $itemRepository,
    ) {
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function show(int $catalog, int $page = 1): TemplateResponse {
        $this->firewall->throwIfNotGranted('ROLE_CATALOG_SHOW');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog);

        $pagination = $this->itemRepository->findPage(
            $page,
            12,
            $catalog->getId(),
            $this->request
        );

        return new TemplateResponse('page/catalog/show.html.twig', [
            'catalog' => $catalog,
            'pagination' => $pagination,
        ]);
    }

    public function add(): TemplateResponse|RedirectResponse {
        return new RedirectResponse('homepage_app');
    }

    public function remove(): RedirectResponse {
        return new RedirectResponse('homepage_app');
    }

    public function setDefault(): RedirectResponse {
        return new RedirectResponse('homepage_app');
    }

    public function edit(): TemplateResponse|RedirectResponse {
        return new RedirectResponse('homepage_app');
    }

    public function sharing(): TemplateResponse {
        return new RedirectResponse('homepage_app');
    }

    public function toggleSharing(): RedirectResponse {
        return new RedirectResponse('homepage_app');
    }

    public function addSharedUser(): RedirectResponse {
        return new RedirectResponse('homepage_app');
    }

    public function removeSharedUser(): RedirectResponse {
        return new RedirectResponse('homepage_app');
    }
}