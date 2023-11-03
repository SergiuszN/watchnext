<?php

namespace WatchNext\WatchNext\Domain\Item\Form;

use WatchNext\Engine\Request\Form;

class EditItemNoteForm extends Form
{
    public string $note;

    public function load(): EditItemNoteForm
    {
        if ($this->isPost) {
            $this->note = $this->request->post('note', '');
        }

        return $this;
    }
}
