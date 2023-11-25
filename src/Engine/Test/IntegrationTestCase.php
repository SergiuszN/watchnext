<?php

namespace WatchNext\Engine\Test;

use PHPUnit\Framework\TestCase;
use WatchNext\Engine\Container;

class IntegrationTestCase extends TestCase
{
    protected Container $container;
    protected Client $client;

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->container = new Container();
        $this->client = $this->container->get(Client::class);
    }
}
