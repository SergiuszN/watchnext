<?php

namespace WatchNext\WatchNext\Domain\Catalog\EventSubscriber;

use WatchNext\Engine\Event\EventSubscriberInterface;
use WatchNext\Engine\Template\Translator;
use WatchNext\WatchNext\Domain\Catalog\Catalog;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;
use WatchNext\WatchNext\Domain\Catalog\CatalogUser;

readonly class CreateDefaultUserCatalogEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CatalogRepository $catalogRepository,
        private Translator $language
    ) {
    }

    public function execute(object $event): void
    {
        $catalog = Catalog::create(
            $this->language->trans('command.createDefaultUserCatalog.defaultCatalog'),
            $event->userId
        );

        $this->catalogRepository->save($catalog);
        $catalogUser = new CatalogUser($catalog->getId(), $event->userId);

        $this->catalogRepository->addAccess($catalogUser);
        $this->catalogRepository->setAsDefault($catalogUser);
    }
}
