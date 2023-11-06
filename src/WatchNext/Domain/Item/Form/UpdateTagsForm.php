<?php

namespace WatchNext\WatchNext\Domain\Item\Form;

use InvalidArgumentException;
use WatchNext\Engine\Request\Form;
use WatchNext\Engine\Security\FlashBag;
use Webmozart\Assert\Assert;

class UpdateTagsForm extends Form
{
    public array $tags;

    public function load(): UpdateTagsForm
    {
        if ($this->isPost) {
            $this->tags = $this->request->post('tags', []);
        }

        return $this;
    }

    public function isValid(bool $csfr = true): bool
    {
        if (parent::isValid($csfr)) {
            try {
                Assert::isArray($this->tags, "tags:{$this->t->trans('item.updateTags.assert.tags.array')}");
            } catch (InvalidArgumentException $invalidArgumentException) {
                (new FlashBag())->addValidationErrors($invalidArgumentException);

                return false;
            }

            return true;
        }

        return false;
    }
}