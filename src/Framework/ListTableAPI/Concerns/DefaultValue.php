<?php

namespace Give\Framework\ListTableAPI\Concerns;

/**
 * @unreleased
 */
trait DefaultValue
{
    /**
     * @var mixed
     */
    protected $defaultValue;

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
}
