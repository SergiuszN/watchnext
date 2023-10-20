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

        echo "<pre>";
        var_dump('ROLE_USER_VIEW');
        var_dump($this->firewall->isGranted('ROLE_USER_VIEW'));


        echo "</pre>";

        return new TemplateResponse('page/homepage/app.html.twig');
    }
}