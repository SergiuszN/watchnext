<?php

namespace WatchNext\Engine\Event;

use WatchNext\Engine\Config;

class KernelEventRegistration {
    public function register(): void {
        $events = (new Config())->get('eventListeners.php');
        $eventDispatcher = new EventDispatcher();

        foreach ($events['kernel.request'] as $requestEvent) {
            $eventDispatcher->subscribeTo('kernel.request', $requestEvent);
        }

        foreach ($events['kernel.response'] as $requestEvent) {
            $eventDispatcher->subscribeTo('kernel.response', $requestEvent);
        }

        foreach ($events['kernel.exception'] as $requestEvent) {
            $eventDispatcher->subscribeTo('kernel.exception', $requestEvent);
        }
    }
}