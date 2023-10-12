<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasDefaultValue
{

    /** @var mixed|null */
    protected $defaultValue;

    /**
     * @param mixed $defaultValue
     */
    public function defaultValue($defaultValue): self
    {
        if (is_string($defaultValue) && empty($defaultValue)) {
            $this->defaultValue = null;
        } else {
            $this->defaultValue = $defaultValue;
        }

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return mixed|null
     */
    public function getSelected()
    {
        return $this->getDefaultValue();
    }
}
