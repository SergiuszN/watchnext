<?php

namespace WatchNext\WatchNext\Domain\Catalog\Form;

use InvalidArgumentException;
use WatchNext\Engine\Request\Form;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Session\CSFR;
use WatchNext\Engine\Session\FlashBag;
use Webmozart\Assert\Assert;

class AddEditCatalogForm extends Form {
    public string $name;

    public function __construct(Request $request, CSFR $csfr) {
        parent::__construct($request, $csfr);

        if ($this->isPost) {
            $this->name = $request->post('name', '');
        }
    }

    public function isValid(): bool {
        if (parent::isValid()) {
            try {
                Assert::minLength($this->name, 3, "name:{$this->t->trans('catalog.add.assert.name.minLength')}");
            } catch (InvalidArgumentException $invalidArgumentException) {
                (new FlashBag())->addValidationErrors($invalidArgumentException);
                return false;
            }

            return true;
        }

        return false;
    }
}