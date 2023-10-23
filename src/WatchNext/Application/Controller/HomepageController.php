<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Session\Auth;
use WatchNext\Engine\Session\SecurityFirewall;
use WatchNext\WatchNext\Domain\Item\ItemRepository;

class HomepageController {
    public function __construct(
        private SecurityFirewall $firewall,
        private ItemRepository $itemRepository,
        private Auth $auth,
    ) {
    }

    public function index(): TemplateResponse {
        return new TemplateResponse('page/homepage/index.html.twig');
    }

    public function app(): TemplateResponse {
        $this->firewall->throwIfNotGranted('ROLE_HOMEPAGE_APP');

        $items = $this->itemRepository->findAllForUser($this->auth->getUserId());

        return new TemplateResponse('page/homepage/app.html.twig', [
            'items' => $items,
        ]);
    }
}