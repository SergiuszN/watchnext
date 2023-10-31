<?php

namespace WatchNext\WatchNext\Application\Controller;

use Exception;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Session\CSFR;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Session\Security;
use WatchNext\WatchNext\Domain\Catalog\CatalogItem;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;
use WatchNext\WatchNext\Domain\Item\ItemCurlBuilder;
use WatchNext\WatchNext\Domain\Item\ItemRepository;

readonly class ItemController
{
    public function __construct(
        private Request $request,
        private ItemRepository $itemRepository,
        private CatalogRepository $catalogRepository,
        private Security $security,
        private CSFR $csfr,
        private FlashBag $flashBag,
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
}
