<?php

namespace WatchNext\WatchNext\Domain\Item;

use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Security\Security;
use WatchNext\Engine\Security\VoterInterface;

class ItemVoter implements VoterInterface
{
    public const VIEW = 'view';

    public function __construct(
        private readonly Security $security,
        private readonly ItemRepository $itemRepository
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function throwIfNotGranted($model, string $action): void
    {
        /** @var Item $item */
        $item = $model;

        if (!$this->isGranted($item, $action)) {
            throw new NotFoundException();
        }
    }

    /**
     * @throws NotFoundException
     */
    public function isGranted($model, string $action): bool
    {
        /** @var Item $item */
        $item = $model;

        if (!$item) {
            throw new NotFoundException();
        }

        $userId = $this->security->getUserId();

        return match ($action) {
            self::VIEW => $this->canView($item, $userId),
            default => false,
        };
    }

    private function canView(Item $item, int $userId): bool
    {
        if ($item->getOwner() === $userId) {
            return true;
        }

        return $this->itemRepository->hasAccess($item->getId(), $userId);
    }
}
