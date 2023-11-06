<?php

namespace WatchNext\WatchNext\Application\Controller;

use DateTimeImmutable;
use Exception;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\RedirectRefererResponse;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Security\CSFR;
use WatchNext\Engine\Security\FlashBag;
use WatchNext\Engine\Security\Security;
use WatchNext\Engine\Template\Translator;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;
use WatchNext\WatchNext\Domain\Catalog\CatalogVoter;
use WatchNext\WatchNext\Domain\Item\Form\EditItemNoteForm;
use WatchNext\WatchNext\Domain\Item\Form\MoveOrCopyItemForm;
use WatchNext\WatchNext\Domain\Item\ItemCurlBuilder;
use WatchNext\WatchNext\Domain\Item\ItemRepository;
use WatchNext\WatchNext\Domain\Item\ItemVoter;

readonly class ItemController
{
    public function __construct(
        private Request $request,
        private ItemRepository $itemRepository,
        private CatalogRepository $catalogRepository,
        private ItemVoter $itemVoter,
        private CatalogVoter $catalogVoter,
        private Security $security,
        private CSFR $csfr,
        private FlashBag $flashBag,
        private Translator $t,
        private EditItemNoteForm $editItemNoteForm,
        private MoveOrCopyItemForm $moveOrCopyItemForm,
    ) {
    }

    /**
     * @throws AccessDeniedException|Exception
     */
    public function add(): TemplateResponse|RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_ITEM_ADD');
        $userId = $this->security->getUserId();

        if ($this->request->isPost()) {
            $this->csfr->throwIfNotValid($this->request->post('csfr'));

            $item = (new ItemCurlBuilder($this->request->post('url')))
                ->load()
                ->setCatalog($this->request->post('catalog'))
                ->setOwner($userId)
                ->parse()
                ->getItem()
                ->setOwner($userId);

            $this->itemRepository->save($item);
            $this->flashBag->add('success', 'New item added to your library');

            return new RedirectResponse('homepage_app');
        }

        return new TemplateResponse('page/item/add.html.twig', [
            'catalogs' => $this->catalogRepository->findAllForUser($userId),
        ]);
    }

    /**
     * @throws AccessDeniedException|NotFoundException
     */
    public function toggleWatched($item): RedirectRefererResponse
    {
        $this->security->throwIfNotGranted('ROLE_ITEM_TOGGLE_WATCHED');
        $item = $this->itemRepository->find($item);
        $this->itemVoter->throwIfNotGranted($item, ItemVoter::VIEW);

        $item->toggleWatched();
        $this->itemRepository->save($item);
        $this->flashBag->add('success', $this->t->trans('item.toggleWatched.success'));

        return new RedirectRefererResponse();
    }

    /**
     * @throws AccessDeniedException|NotFoundException
     */
    public function note($item): RedirectRefererResponse
    {
        $this->security->throwIfNotGranted('ROLE_ITEM_NOTE');
        $item = $this->itemRepository->find($item);
        $this->itemVoter->throwIfNotGranted($item, ItemVoter::VIEW);

        $form = $this->editItemNoteForm->load();

        if ($form->isValid()) {
            $item->setNote($form->note);
            $this->itemRepository->save($item);

            $this->flashBag->add('success', $this->t->trans('item.note.success'));

            return new RedirectRefererResponse();
        }

        $this->flashBag->add('error', $this->t->trans('item.note.error'));

        return new RedirectRefererResponse();
    }

    /**
     * @throws AccessDeniedException
     */
    public function search(int $page = 1): TemplateResponse
    {
        $this->security->throwIfNotGranted('ROLE_ITEM_SEARCH');

        $pagination = $this->itemRepository->findSearchPage(
            $page,
            12,
            $this->security->getUserId(),
            $this->request
        );

        return new TemplateResponse('page/item/search.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function delete($item): RedirectRefererResponse
    {
        $this->security->throwIfNotGranted('ROLE_ITEM_DELETE');
        $item = $this->itemRepository->find($item);
        $this->itemVoter->throwIfNotGranted($item, ItemVoter::VIEW);

        $this->itemRepository->delete($item);
        $this->flashBag->add('success', $this->t->trans('item.delete.success'));

        return new RedirectRefererResponse();
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function move($item): TemplateResponse|RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_ITEM_MOVE');
        $item = $this->itemRepository->find($item);
        $this->itemVoter->throwIfNotGranted($item, ItemVoter::VIEW);

        $form = $this->moveOrCopyItemForm->load();

        if ($form->isValid()) {
            $toCatalog = $this->catalogRepository->find($form->catalog);
            $this->catalogVoter->throwIfNotGranted($toCatalog, CatalogVoter::VIEW);

            $item->setCatalog($toCatalog->getId());
            $item->setAddedAt(new DateTimeImmutable());
            $this->itemRepository->save($item);

            $this->flashBag->add('success', $this->t->trans('item.move.success'));

            return new RedirectResponse('catalog_show', ['catalog' => $toCatalog->getId()]);
        }

        return new TemplateResponse('page/item/move.html.twig', [
            'catalogs' => $this->catalogRepository->findAllForUser($this->security->getUserId()),
        ]);
    }

    /**
     * @throws NotFoundException|AccessDeniedException
     */
    public function copy($item): TemplateResponse|RedirectResponse
    {
        $this->security->throwIfNotGranted('ROLE_ITEM_COPY');
        $item = $this->itemRepository->find($item);
        $this->itemVoter->throwIfNotGranted($item, ItemVoter::VIEW);

        $form = $this->moveOrCopyItemForm->load();

        if ($form->isValid()) {
            $toCatalog = $this->catalogRepository->find($form->catalog);
            $this->catalogVoter->throwIfNotGranted($toCatalog, CatalogVoter::VIEW);

            $copyItem = clone $item;
            $copyItem->setId(null);
            $copyItem->setCatalog($toCatalog->getId());
            $copyItem->setAddedAt(new DateTimeImmutable());
            $this->itemRepository->save($copyItem);

            $this->flashBag->add('success', $this->t->trans('item.copy.success'));

            return new RedirectResponse('catalog_show', ['catalog' => $toCatalog->getId()]);
        }

        return new TemplateResponse('page/item/copy.html.twig', [
            'catalogs' => $this->catalogRepository->findAllForUser($this->security->getUserId()),
        ]);
    }
}
