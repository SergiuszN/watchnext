<?php

namespace Integration\Controller;

use WatchNext\Engine\Test\IntegrationTestCase;

class HomepageControllerTest extends IntegrationTestCase
{
    public function testIndex(): void
    {
        $this->client->get('/');

        self::assertTrue($this->client->contains('<h1 class="text-body-emphasis">WatchNext</h1>'), 'Homepage should contain WatchNext title');
        self::assertTrue($this->client->isCode(200), 'Homepage should return 200 code');
    }

    public function testApp(): void
    {
        $this->client->login('test1');
        $this->client->get('/app');

        self::assertEquals(302, $this->client->getCode(), 'App should return 302 code');
    }
}
