<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * Trait HasSettersAndGetters
 *
 * @unreleased
 */
trait HasGettersAndSetters
{
    use HasGetters {
        HasGetters::__call as protected callGetters;
    }
    use HasSetters {
        HasSetters::__call as protected callSetters;
    }

    public function __call($name, $arguments = null)
    {
        if ($arguments) {
            return $this->callSetters($name, $arguments);
        } else {
            return $this->callGetters($name, $arguments);
        }
    }
}
