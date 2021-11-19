<?php

namespace Give\FormAPI\Form;

class Radio extends Field
{
    /**
     * Field options.
     *
     * @sicne 2.7.0
     * @var array
     */
    public $options = [];

    /**
     * Field display style.
     *
     * @since 2.7.0
     * @var string
     */
    public $displayStyle = '';

    /**
     * @inheritDoc
     */
    public function parse($array)
    {
        parent::parse($array);

        $this->options = isset($array['options']) ? $array['options'] : [];

        $type = explode('_', $this->type, 2);
        $this->displayStyle = false !== strpos('_', $this->type) ? array_pop($type) : '';
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
