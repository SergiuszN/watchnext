<?php

namespace WatchNext\WatchNext\Domain\Catalog\Form;

use InvalidArgumentException;
use WatchNext\Engine\Request\Form;
use WatchNext\Engine\Session\FlashBag;
use Webmozart\Assert\Assert;

class AddEditCatalogForm extends Form
{
    public string $name;

    public function load(): AddEditCatalogForm
    {
        if ($this->isPost) {
            $this->name = $this->request->post('name', '');
        }

        return $this;
    }

    public function isValid(bool $csfr = true): bool
    {
        if (parent::isValid($csfr)) {
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
