<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasLabel
{

    /** @var string */
    protected $label;

    /**
     * @param string $label
     *
     * @return $this
     */
    public function label($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
