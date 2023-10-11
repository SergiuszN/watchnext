<?php

namespace WatchNext\App\Application\Controller;

use WatchNext\Engine\Response\TemplateResponse;

class HomepageController {
    public function index(): TemplateResponse {
        return new TemplateResponse('index.html.twig');
    }
}