<?php

namespace Give\Framework\ListTable\Concerns;

/**
 * @unreleased
 */
trait DefaultValue
{
    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * Set column default value
     *
     * @unreleased
     */
    public function defaultValue(string $value): self
    {
        $this->defaultValue = $value;
        return $this;
    }

    /**
     * @unreleased
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
