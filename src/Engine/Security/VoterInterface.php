<?php

namespace WatchNext\Engine\Security;

use WatchNext\Engine\Router\NotFoundException;

interface VoterInterface
{
    /**
     * @throws NotFoundException
     */
    public function throwIfNotGranted($model, string $action): void;

    /**
     * @throws NotFoundException
     */
    public function isGranted($model, string $action): bool;
}
