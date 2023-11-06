<?php

namespace WatchNext\WatchNext\Application\Controller;

use Exception;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\RedirectRefererResponse;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Session\CSFR;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Session\Security;
use WatchNext\Engine\Template\Translator;
use WatchNext\WatchNext\Domain\Catalog\CatalogItem;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;
use WatchNext\WatchNext\Domain\Item\Form\EditItemNoteForm;
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
        private Security $security,
        private CSFR $csfr,
        private FlashBag $flashBag,
        private Translator $t,
        private EditItemNoteForm $editItemNoteForm,
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
                ->parse()
                ->getItem()
                ->setOwner($userId);

            $this->itemRepository->save($item);
            $this->catalogRepository->addItem(new CatalogItem($item->getId(), $this->request->post('catalog')));

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
}
