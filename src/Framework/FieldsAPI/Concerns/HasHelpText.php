<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasHelpText
{

    /** @var string */
    protected $helpText;

    /**
     * @param string $helpText
     *
     * @return $this
     */
    public function helpText($helpText)
    {
        $this->helpText = $helpText;

        return $this;
    }

    public function getHelpText()
    {
        return $this->helpText;
    }
}
