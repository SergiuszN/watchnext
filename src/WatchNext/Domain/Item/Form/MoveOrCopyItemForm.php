<?php

namespace WatchNext\WatchNext\Domain\Item\Form;

use InvalidArgumentException;
use WatchNext\Engine\Request\Form;
use WatchNext\Engine\Security\FlashBag;
use Webmozart\Assert\Assert;

class MoveOrCopyItemForm extends Form
{
    public int $catalog;

    public function load(): MoveOrCopyItemForm
    {
        if ($this->isPost) {
            $this->catalog = $this->request->post('catalog');
        }

        return $this;
    }

    public function isValid(bool $csfr = true): bool
    {
        if (parent::isValid($csfr)) {
            try {
                Assert::numeric($this->catalog, "catalog:{$this->t->trans('item.moveOrCopy.assert.catalog.numeric')}");
            } catch (InvalidArgumentException $invalidArgumentException) {
                (new FlashBag())->addValidationErrors($invalidArgumentException);

                return false;
            }

            return true;
        }

        return false;
    }
}
