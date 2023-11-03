<?php

namespace WatchNext\WatchNext\Domain\Item;

use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Session\Security;

class ItemVoter
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
    public function throwIfNotGranted(?Item $item, string $action): void
    {
        if (!$this->isGranted($item, $action)) {
            throw new NotFoundException();
        }
    }

    /**
     * @throws NotFoundException
     */
    public function isGranted(?Item $item, string $action): bool
    {
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
