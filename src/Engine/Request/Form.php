<?php

namespace WatchNext\Engine\Request;

use InvalidArgumentException;
use WatchNext\Engine\Session\CSFR;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Template\Language;
use Webmozart\Assert\Assert;

class Form {
    protected bool $isPost;
    protected readonly string $token;

    public function __construct(protected Request $request, protected CSFR $csfr, protected Language $t) {
        $this->isPost = $this->request->isPost();
        $this->token = $this->request->post('_token', '');
    }

    public function load(): self {
        return $this;
    }

    public function isValid(bool $csfr = true): bool {
        if (!$this->isPost) {
            return false;
        }

        try {
            if ($csfr) {
                Assert::true($this->csfr->validate($this->token), "token:{$this->t->trans('csfr.token.invalid')}");
            }
        } catch (InvalidArgumentException $invalidArgumentException) {
            (new FlashBag())->addValidationErrors($invalidArgumentException);
            return false;
        }

        return true;
    }
}