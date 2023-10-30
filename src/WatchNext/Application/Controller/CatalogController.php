<?php

namespace WatchNext\WatchNext\Application\Controller;

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
use WatchNext\WatchNext\Domain\Catalog\Form\AddEditCatalogForm;
use WatchNext\WatchNext\Domain\Item\ItemRepository;
use WatchNext\WatchNext\Domain\User\Query\UserCreatedQuery;
use WatchNext\WatchNext\Domain\User\UserRepository;

readonly class CatalogController {
    public function __construct(
        private Request $request,
        private CatalogRepository $catalogRepository,
        private ItemRepository $itemRepository,
        private UserRepository $userRepository,
        private SecurityFirewall $firewall,
        private CatalogVoter $catalogVoter,
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
    public function manage(): TemplateResponse {
        $this->firewall->throwIfNotGranted('ROLE_CATALOG_MANAGE');
        $userId = $this->auth->getUserId();
        $catalogs = $this->catalogRepository->findAllForUser($userId);

        return new TemplateResponse('page/catalog/manage.html.twig', [
            'ownedCatalogs' => array_filter($catalogs, fn (Catalog $catalog) => $catalog->getOwner() === $userId),
            'sharedCatalogs' => array_filter($catalogs, fn (Catalog $catalog) => $catalog->getOwner() !== $userId),
        ]);
    }

    /**
     * @throws AccessDeniedException
     */
    public function add(): TemplateResponse|RedirectResponse {
        $this->firewall->throwIfNotGranted('ROLE_CATALOG_ADD');
        $form = new AddEditCatalogForm($this->request, $this->csfr);
        $userId = $this->auth->getUserId();

        if ($form->isValid()) {
            $catalog = Catalog::create($form->name, $userId);
            $this->catalogRepository->save($catalog);

            $catalogUser = new CatalogUser($catalog->getId(), $userId);
            $this->catalogRepository->addAccess($catalogUser);

            $this->flashBag->add('success', $this->language->trans('catalog.add.success'));
            return new RedirectResponse('catalog_manage');
        }

        return new TemplateResponse('page/catalog/add.html.twig');
    }

    /**
     * @throws NotFoundException|AccessDeniedException
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
        return new RedirectResponse('catalog_manage');
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function setDefault(int $catalog): RedirectResponse {
        $this->firewall->throwIfNotGranted('ROLE_CATALOG_SET_DEFAULT');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::EDIT);
        $this->catalogRepository->setAsDefault(new CatalogUser($catalog->getId(), $catalog->getOwner()));

        $this->flashBag->add('success', $this->language->trans('catalog.setDefault.success'));
        return new RedirectResponse('catalog_manage');
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function edit(int $catalog): TemplateResponse|RedirectResponse {
        $this->firewall->throwIfNotGranted('ROLE_CATALOG_EDIT');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::EDIT);

        $form = new AddEditCatalogForm($this->request, $this->csfr);

        if ($form->isValid()) {
            $catalog->setName($form->name);
            $this->catalogRepository->save($catalog);

            $this->flashBag->add('success', $this->language->trans('catalog.edit.success'));
            return new RedirectResponse('catalog_manage');
        }

        return new TemplateResponse('page/catalog/edit.html.twig', [
            'catalog' => $catalog,
            'sharedWith' => $this->userRepository->findSharedWithUsersForCatalog($catalog->getId()),
        ]);
    }

    public function addSharedUser(int $catalog): RedirectResponse {
        return new RedirectResponse('homepage_app');
    }

    public function removeSharedUser(int $catalog, int $user): RedirectResponse {
        return new RedirectResponse('homepage_app');
    }

    public function unsubscribe(int $catalog): RedirectResponse {
        return new RedirectResponse('homepage_app');
    }
}