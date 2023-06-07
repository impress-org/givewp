<?php

namespace Give\Framework\FieldsAPI\Concerns;

use BadMethodCallException;

/**
 * Trait HasGetters
 *
 * @unreleased
 */
trait HasGetters
{
    public function __call($name, $arguments)
    {
        $prefix = substr($name, 0, 3);
        $property = lcfirst(substr($name, 3));

        if (!property_exists($this, $property)) {
            throw new BadMethodCallException(sprintf(__('Property %s does not exist', 'givewp'), $name));
        }

        if ($prefix === 'get') {
            return $this->$property;
        }

        throw new BadMethodCallException(sprintf(__('Method %s does not exist', 'givewp'), $name));
    }
}
