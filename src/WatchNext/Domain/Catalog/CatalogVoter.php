<?php

namespace WatchNext\WatchNext\Domain\Catalog;

use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Security\Security;
use WatchNext\Engine\Security\VoterInterface;

readonly class CatalogVoter implements VoterInterface
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    public function __construct(
        private Security $security,
        private CatalogRepository $catalogRepository,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function throwIfNotGranted($model, string $action): void
    {
        /** @var Catalog $catalog */
        $catalog = $model;

        if (!$this->isGranted($catalog, $action)) {
            throw new NotFoundException();
        }
    }

    /**
     * @throws NotFoundException
     */
    public function isGranted($model, string $action): bool
    {
        /** @var Catalog $catalog */
        $catalog = $model;

        if (!$catalog) {
            throw new NotFoundException();
        }

        $userId = $this->security->getUserId();

        return match ($action) {
            self::VIEW => $this->canView($catalog, $userId),
            self::EDIT => $this->canEdit($catalog, $userId),
            default => false,
        };
    }

    private function canEdit(Catalog $catalog, int $userId): bool
    {
        return $catalog->getOwner() === $userId;
    }

    private function canView(Catalog $catalog, int $userId): bool
    {
        if ($catalog->getOwner() === $userId) {
            return true;
        }

        if ($this->catalogRepository->hasAccess(new CatalogUser($catalog->getId(), $userId))) {
            return true;
        }

        return false;
    }
}
