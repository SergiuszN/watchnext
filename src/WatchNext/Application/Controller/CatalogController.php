<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Session\Security;
use WatchNext\Engine\Template\Translator;
use WatchNext\WatchNext\Domain\Catalog\Catalog;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;
use WatchNext\WatchNext\Domain\Catalog\CatalogUser;
use WatchNext\WatchNext\Domain\Catalog\CatalogVoter;
use WatchNext\WatchNext\Domain\Catalog\Form\AddEditCatalogForm;
use WatchNext\WatchNext\Domain\Catalog\Form\CatalogShareWithForm;
use WatchNext\WatchNext\Domain\Catalog\SetDefaultCatalogIfRemoved;
use WatchNext\WatchNext\Domain\Item\ItemRepository;
use WatchNext\WatchNext\Domain\User\UserRepository;

readonly class CatalogController
{
    public function __construct(
        private Request $request,
        private CatalogRepository $catalogRepository,
        private ItemRepository $itemRepository,
        private UserRepository $userRepository,
        private CatalogVoter $catalogVoter,
        private Security $security,
        private FlashBag $flashBag,
        private Translator $language,
        private SetDefaultCatalogIfRemoved $setDefaultCatalogIfRemoved,
        private AddEditCatalogForm $addEditCatalogForm,
        private CatalogShareWithForm $catalogShareWithForm,
    ) {
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function show(int $catalog, int $page = 1): TemplateResponse
    {
        $this->security->throwIfNotGranted('ROLE_CATALOG_SHOW');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::VIEW);

        $pagination = $this->itemRepository->findCatalogPage(
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
    public function manage(): TemplateResponse
    {
        $this->security->throwIfNotGranted('ROLE_CATALOG_MANAGE');
        $userId = $this->security->getUserId();
        $catalogs = $this->catalogRepository->findAllForUser($userId);

        return new TemplateResponse('page/catalog/manage.html.twig', [
            'ownedCatalogs' => array_filter($catalogs, fn (Catalog $catalog) => $catalog->getOwner() === $userId),
            'sharedCatalogs' => array_filter($catalogs, fn (Catalog $catalog) => $catalog->getOwner() !== $userId),
        ]);
    }

    /**
     * @throws AccessDeniedException
     */
    public function add(): TemplateResponse|RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_CATALOG_ADD');
        $form = $this->addEditCatalogForm->load();
        $userId = $this->security->getUserId();

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
    public function remove(int $catalog): RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_CATALOG_REMOVE');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::EDIT);

        $removedDefaults = $this->catalogRepository->remove($catalog);

        foreach ($removedDefaults as $removedDefault) {
            $this->setDefaultCatalogIfRemoved->execute($removedDefault->user);
        }

        $this->flashBag->add('success', $this->language->trans('catalog.remove.success'));

        return new RedirectResponse('catalog_manage');
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function setDefault(int $catalog): RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_CATALOG_SET_DEFAULT');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::VIEW);
        $this->catalogRepository->setAsDefault(new CatalogUser($catalog->getId(), $this->security->getUserId()));

        $this->flashBag->add('success', $this->language->trans('catalog.setDefault.success'));

        return new RedirectResponse('catalog_manage');
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function edit(int $catalog): TemplateResponse|RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_CATALOG_EDIT');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::EDIT);

        $form = $this->addEditCatalogForm->load();

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

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function share(int $catalog): RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_CATALOG_SHARE');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::EDIT);

        $form = $this->catalogShareWithForm->load();

        if ($form->isValid()) {
            $user = $this->userRepository->findByLogin($form->username);

            if ($user) {
                $this->catalogRepository->addAccess(new CatalogUser($catalog->getId(), $user->getId()));
                $this->flashBag->add('success', $this->language->trans('catalog.addSharedUser.success'));
            } else {
                $this->flashBag->add('error', $this->language->trans('catalog.addSharedUser.error', ['%user%' => $form->username]));
            }
        }

        return new RedirectResponse('catalog_edit', ['catalog' => $catalog->getId()]);
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function unShare(int $catalog, int $user): RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_CATALOG_UN_SHARE_TO');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::EDIT);

        $defaultUserId = $this->catalogRepository->removeAccess(new CatalogUser($catalog->getId(), $user));
        $this->setDefaultCatalogIfRemoved->execute($defaultUserId);

        $this->flashBag->add('success', $this->language->trans('catalog.unShareTo.success'));

        return new RedirectResponse('catalog_edit', ['catalog' => $catalog->getId()]);
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function unsubscribe(int $catalog): RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_CATALOG_UNSUBSCRIBE');
        $catalog = $this->catalogRepository->find($catalog);
        $this->catalogVoter->throwIfNotGranted($catalog, CatalogVoter::VIEW);

        $defaultUserId = $this->catalogRepository->removeAccess(new CatalogUser($catalog->getId(), $this->security->getUserId()));
        $this->setDefaultCatalogIfRemoved->execute($defaultUserId);

        $this->flashBag->add('success', $this->language->trans('catalog.unsubscribe.success'));

        return new RedirectResponse('catalog_manage');
    }
}
