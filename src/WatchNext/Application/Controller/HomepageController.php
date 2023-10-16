<?php

namespace WatchNext\WatchNext\Application\Controller;

use Symfony\Component\Mime\Email;
use WatchNext\Engine\Mail\Mailer;
use WatchNext\Engine\Response\TemplateResponse;

class HomepageController {
    public function __construct(private Mailer $mailer) {
    }

    public function index(): TemplateResponse {
        $this->mailer->send(
            (new Email())
            ->subject('Test message')
            ->from('sender@example.com')
            ->to('reciever@example.com')
            ->html('<h1>Hello world</h1><p>Test paragraph!</p>')
        );

        return new TemplateResponse('index.html.twig');
    }
}