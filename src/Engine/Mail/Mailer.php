<?php

namespace WatchNext\Engine\Mail;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

class Mailer {
    private static ?TransportInterface $client = null;

    public function __construct() {
        if (self::$client !== null) {
            return;
        }

        self::$client = Transport::fromDsn($_ENV['MAILER_DSN']);
    }

    public function send(Email $mail): void {
        self::$client->send($mail);
    }
}