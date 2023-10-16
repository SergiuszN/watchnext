<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Response\TemplateResponse;

class HomepageController {
    public function __construct() {
    }

    public function index(): TemplateResponse {
        return new TemplateResponse('index.html.twig');
    }
}