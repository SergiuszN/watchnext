<?php

namespace WatchNext\Engine\Request;

use InvalidArgumentException;
use WatchNext\Engine\Security\CSFR;
use WatchNext\Engine\Security\FlashBag;
use WatchNext\Engine\Template\Translator;
use Webmozart\Assert\Assert;

class Form
{
    protected bool $isPost;
    protected readonly string $token;

    public function __construct(protected Request $request, protected CSFR $csfr, protected Translator $t)
    {
        $this->isPost = $this->request->isPost();
        $this->token = $this->request->request('_token', '');
    }

    public function load(): self
    {
        return $this;
    }

    public function isValid(bool $csfr = true): bool
    {
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
