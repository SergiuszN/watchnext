<?php

namespace WatchNext\WatchNext\Application\Controller;

use Exception;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Session\Auth;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Session\SecurityFirewall;
use WatchNext\WatchNext\Domain\Item\ItemCurlBuilder;
use WatchNext\WatchNext\Domain\Item\ItemRepository;

readonly class ItemController {
    public function __construct(
        private SecurityFirewall $firewall,
        private Request          $request,
        private ItemRepository   $itemRepository,
        private Auth             $auth,
        private FlashBag         $flashBag,
    ) {
    }

    /**
     * @throws AccessDeniedException|Exception
     */
    public function add(): TemplateResponse|RedirectResponse {
        $this->firewall->throwIfNotGranted('ROLE_ITEM_ADD');

        if ($this->request->isPost()) {
            $item = (new ItemCurlBuilder($this->request->post('url')))
                ->load()
                ->parse()
                ->getItem()
                ->setOwner($this->auth->getUserId());

            $this->itemRepository->save($item);
            $this->flashBag->add('success', 'New item added to your library');
            return new RedirectResponse('homepage_app');
        }

        return new TemplateResponse('page/item/add.html.twig');
    }
}