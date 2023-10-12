<?php

namespace WatchNext\WatchNext\Application\Controller;

use WatchNext\Engine\Response\JsonResponse;
use WatchNext\Engine\Response\TemplateResponse;

class HomepageController {
    public function index(): TemplateResponse {
        return new TemplateResponse('index.html.twig');
    }

    public function test(): JsonResponse {
        return new JsonResponse(['data' => 'some data', 'status' => 'ok']);
    }
}