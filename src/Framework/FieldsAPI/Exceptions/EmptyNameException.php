<?php

namespace Give\Framework\FieldsAPI\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @since 2.16.0
 */
class EmptyNameException extends Exception
{
    public function __construct($code = 0, Exception $previous = null)
    {
        $message = "Node name can not be empty";
        parent::__construct($message, $code, $previous);
    }
}
