<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Response\TemplateResponse;

class HomepageController {
    public function __construct() {
    }

    public function index(): TemplateResponse {
        return new TemplateResponse('page/homepage/index.html.twig');
    }

    public function app(): TemplateResponse {
        return new TemplateResponse('page/homepage/app.html.twig');
    }
}