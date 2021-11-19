<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasDefaultValue
{

    /** @var string */
    protected $defaultValue;

    /**
     * @param string|array $defaultValue
     *
     * @return $this
     */
    public function defaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return string|array
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return string|array
     */
    public function getSelected()
    {
        return $this->getDefaultValue();
    }
}
