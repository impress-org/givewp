<?php

namespace Give\Framework\FieldsAPI\Conditions;

use JsonSerializable;

/**
 * @since 2.13.0
 */
abstract class Condition implements JsonSerializable
{

    /**
     * @since 2.13.0
     *
     * {@inheritDoc}
     */
    abstract public function jsonSerialize();
}
