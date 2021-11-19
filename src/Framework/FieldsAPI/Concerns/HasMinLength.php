<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @since 2.14.0
 *
 * @property ValidationRules $validationRules
 */
trait HasMinLength
{

    /**
     * Set the value’s minimum length.
     *
     * @since 2.14.0
     *
     * @param int $minLength
     *
     * @return $this
     */
    public function minLength($minLength)
    {
        $this->validationRules->rule('minLength', $minLength);

        return $this;
    }

    /**
     * Get the value’s minimum length.
     *
     * @since 2.14.0
     *
     * @return int|null
     */
    public function getMinLength()
    {
        return $this->validationRules->getRule('minLength');
    }
}
