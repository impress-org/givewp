<?php

namespace Give\FormAPI\Form;

class Select extends Field
{

    /**
     * @var array
     */
    public $options = [];

    /**
     * @inheritDoc
     */
    public function parse($array)
    {
        parent::parse($array);

        $this->options = isset($array[ 'options' ]) ? $array[ 'options' ] : [];
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'options' => $this->options,
            ]
        );
    }
}
