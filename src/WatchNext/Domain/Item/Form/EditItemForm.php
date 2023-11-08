<?php

namespace WatchNext\WatchNext\Domain\Item\Form;

use WatchNext\Engine\Request\Form;

class EditItemForm extends Form
{
    public string $title;
    public string $url;
    public string $description;
    public string $image;

    public function load(): EditItemForm
    {
        if ($this->isPost) {
            $this->title = $this->request->post('title');
            $this->url = $this->request->post('url');
            $this->description = $this->request->post('description');
            $this->image = $this->request->post('image');
        }

        return $this;
    }
}
