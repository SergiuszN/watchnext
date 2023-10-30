<?php

namespace WatchNext\WatchNext\Domain\Catalog\Form;

use InvalidArgumentException;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Session\CSFR;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Template\Language;
use Webmozart\Assert\Assert;

class AddEditCatalogForm {
    private bool $isPost;
    private CSFR $csfr;
    public readonly string $token;
    public string $name;

    public function __construct(Request $request, CSFR $csfr) {
        $this->isPost = $request->isPost();
        $this->csfr = $csfr;

        if ($this->isPost) {
            $this->token = $request->post('_token', '');
            $this->name = $request->post('name', '');
        }
    }

    public function isValid(): bool {
        if (!$this->isPost) {
            return false;
        }

        try {
            $l = new Language();
            Assert::true($this->csfr->validate($this->token), "token:{$l->trans('csfr.token.invalid')}");
            Assert::minLength($this->name, 3, "name:{$l->trans('catalog.add.assert.name.minLength')}");
        } catch (InvalidArgumentException $invalidArgumentException) {
            (new FlashBag())->addValidationErrors($invalidArgumentException);
            return false;
        }

        return true;
    }
}