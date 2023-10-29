<?php

namespace WatchNext\WatchNext\Application\Controller;

use Exception;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Database\QueryBuilder;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Session\Auth;
use WatchNext\Engine\Session\CSFR;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Session\SecurityFirewall;
use WatchNext\Engine\Template\Language;
use WatchNext\WatchNext\Domain\Catalog\Catalog;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;
use WatchNext\WatchNext\Domain\Catalog\CatalogUser;
use WatchNext\WatchNext\Domain\Catalog\CatalogVoter;
use WatchNext\WatchNext\Domain\Catalog\Command\CreateDefaultUserCatalogCommand;
use WatchNext\WatchNext\Domain\Catalog\Form\AddCatalogForm;
use WatchNext\WatchNext\Domain\Item\ItemRepository;
use WatchNext\WatchNext\Domain\User\Query\UserCreatedQuery;

readonly class CatalogController {
    public function __construct(
        private Request $request,
        private CatalogRepository $catalogRepository,
        private SecurityFirewall $firewall,
        private CatalogVoter $catalogVoter,
        private ItemRepository $itemRepository,
        private CSFR $csfr,
        private Auth $auth,
        private FlashBag $flashBag,
        private Language $language,
        private CreateDefaultUserCatalogCommand $createDefaultUserCatalogCommand
    ) {
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function show(int $catalog, int $page = 1): TemplateResponse {
        $this->firewall->throwIfNotGranted('ROLE_CATALOG_SHOW');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::VIEW);

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

    /**
     * @throws AccessDeniedException
     */
    public function add(): TemplateResponse|RedirectResponse {
        $this->firewall->throwIfNotGranted('ROLE_CATALOG_ADD');
        $form = new AddCatalogForm($this->request, $this->csfr);
        $userId = $this->auth->getUserId();

        if ($form->isValid()) {
            $catalog = Catalog::create($form->name, $userId);
            $this->catalogRepository->save($catalog);

            $catalogUser = new CatalogUser($catalog->getId(), $userId);
            $this->catalogRepository->addAccess($catalogUser);

            if ($form->default) {
                $this->catalogRepository->setAsDefault($catalogUser);
            }

            $this->flashBag->add('success', $this->language->trans('catalog.add.success'));
            return new RedirectResponse('catalog_show', [
                'catalog' => $this->catalogRepository->findDefaultForUser($userId)->getId()
            ]);
        }

        return new TemplateResponse('page/catalog/add.html.twig');
    }

    /**
     * @throws NotFoundException
     * @throws AccessDeniedException
     */
    public function remove(int $catalog): RedirectResponse {
        $this->firewall->throwIfNotGranted('ROLE_CATALOG_REMOVE');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::EDIT);

        $removedDefaults = $this->catalogRepository->remove($catalog);

        foreach ($removedDefaults as $removedDefault) {
            /** @var Catalog[] $userCatalogs */
            $userCatalogs = $this->catalogRepository->findAllForUser($removedDefault->user);

            if (!empty($userCatalogs)) {
                $this->catalogRepository->setAsDefault(new CatalogUser($userCatalogs[0]->getId(), $removedDefault->user));
            } else {
                $this->createDefaultUserCatalogCommand->execute(new UserCreatedQuery($removedDefault->user));
            }
        }

        $this->flashBag->add('success', $this->language->trans('catalog.remove.success'));
        return new RedirectResponse('catalog_show', [
            'catalog' => $this->catalogRepository->findDefaultForUser($this->auth->getUserId())->getId()
        ]);
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