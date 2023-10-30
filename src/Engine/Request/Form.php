<?php

namespace WatchNext\Engine\Request;

use InvalidArgumentException;
use WatchNext\Engine\Session\CSFR;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Template\Language;
use Webmozart\Assert\Assert;

class Form {
    protected bool $isPost;
    protected ?CSFR $csfr;
    protected Language $t;
    protected readonly string $token;

    public function __construct(Request $request, ?CSFR $csfr = null) {
        $this->isPost = $request->isPost();
        $this->t = new Language();

        if ($csfr) {
            $this->csfr = $csfr;
            $this->token = $request->post('_token', '');
        }
    }

    public function isValid(): bool {
        if (!$this->isPost) {
            return false;
        }

        try {
            Assert::true($this->csfr->validate($this->token), "token:{$this->t->trans('csfr.token.invalid')}");
        } catch (InvalidArgumentException $invalidArgumentException) {
            (new FlashBag())->addValidationErrors($invalidArgumentException);
            return false;
        }

        return true;
    }
}