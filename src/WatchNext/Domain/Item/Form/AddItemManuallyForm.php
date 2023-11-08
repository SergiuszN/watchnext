<?php

namespace WatchNext\WatchNext\Domain\Item\Form;

use WatchNext\Engine\Request\Form;

class AddItemManuallyForm extends Form
{
    public string $title;
    public string $url;
    public string $description;
    public string $image;
    public int $catalog;

    public function load(): AddItemManuallyForm
    {
        if ($this->isPost) {
            $this->title = $this->request->post('title');
            $this->url = $this->request->post('url');
            $this->description = $this->request->post('description');
            $this->image = $this->request->post('image');
            $this->catalog = $this->request->post('catalog');
        }

        return $this;
    }
}
