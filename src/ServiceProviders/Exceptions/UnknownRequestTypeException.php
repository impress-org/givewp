<?php

namespace Give\ServiceProviders;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @since 2.23.1
 */
class UnknownRequestTypeException extends Exception
{
    public function __construct($type, $code = 0, $previous = null)
    {
        parent::__construct("Request $type is not supported", $code, $previous);
    }
}
