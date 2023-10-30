<?php

namespace WatchNext\WatchNext\Domain\Catalog\Form;

use WatchNext\Engine\Request\Form;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Session\CSFR;

class CatalogShareWithForm extends Form {
    public string $username;

    public function __construct(Request $request, CSFR $csfr) {
        parent::__construct($request, $csfr);

        if ($this->isPost) {
            $this->username = $request->post('username', '');
        }
    }
}