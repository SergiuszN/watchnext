<?php

namespace WatchNext\WatchNext\Domain\Catalog\Form;

use WatchNext\Engine\Request\Form;

class CatalogShareWithForm extends Form
{
    public string $username;

    public function load(): CatalogShareWithForm
    {
        if ($this->isPost) {
            $this->username = $this->request->post('username', '');
        }

        return $this;
    }
}
