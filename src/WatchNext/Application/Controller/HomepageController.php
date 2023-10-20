<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Session\SecurityFirewall;

class HomepageController {
    public function __construct(private SecurityFirewall $firewall) {
    }

    public function index(): TemplateResponse {
        return new TemplateResponse('page/homepage/index.html.twig');
    }

    public function app(): TemplateResponse {
        $this->firewall->throwIfNotGranted('ROLE_HOMEPAGE_APP');

        return new TemplateResponse('page/homepage/app.html.twig');
    }
}