<?php

namespace WatchNext\WatchNext\Domain\Catalog\Command;

use WatchNext\Engine\Event\CommandInterface;
use WatchNext\Engine\Template\Language;
use WatchNext\WatchNext\Domain\Catalog\Catalog;
use WatchNext\WatchNext\Domain\Catalog\CatalogRepository;
use WatchNext\WatchNext\Domain\User\Query\UserCreatedQuery;

readonly class CreateDefaultUserCatalogCommand implements CommandInterface {
    public function __construct(
        private CatalogRepository $catalogRepository,
        private Language $language
    ) {
    }

    public function execute(object $query): void {
        /** @var $query UserCreatedQuery */
        $catalog = Catalog::create(
            $this->language->trans('command.createDefaultUserCatalog.defaultCatalog'),
            $query->userId,
            true
        );

        $this->catalogRepository->save($catalog);
    }
}