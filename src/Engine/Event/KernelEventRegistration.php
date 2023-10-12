<?php

namespace WatchNext\Engine\Event;

class KernelEventRegistration {
    public function register(): void {
        $events = require __DIR__ . '/../../../config/eventListeners.php';
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