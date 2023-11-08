<?php

namespace WatchNext\WatchNext\Domain\Item\Form;

use WatchNext\Engine\Request\Form;

class AddItemFromUrlForm extends Form
{
    public string $url;
    public int $catalog;

    public function load(): AddItemFromUrlForm
    {
        if ($this->isPost) {
            $this->url = $this->request->post('url');
            $this->catalog = $this->request->post('catalog');
        }

        return $this;
    }
}
