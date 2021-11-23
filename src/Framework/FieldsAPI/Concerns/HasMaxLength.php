<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @since 2.14.0
 *
 * @property ValidationRules $validationRules
 */
trait HasMaxLength
{

    /**
     * Set the value’s maximum length.
     *
     * @since 2.14.0
     *
     * @param int $maxLength
     *
     * @return $this
     */
    public function maxLength($maxLength)
    {
        $this->validationRules->rule('maxLength', $maxLength);

        return $this;
    }

    /**
     * Get the value’s maximum length.
     *
     * @since 2.14.0
     *
     * @return int|null
     */
    public function getMaxLength()
    {
        return $this->validationRules->getRule('maxLength');
    }
}
