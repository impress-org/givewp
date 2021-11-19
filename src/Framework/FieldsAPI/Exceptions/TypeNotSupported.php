<?php

namespace Give\Framework\FieldsAPI\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;

class TypeNotSupported extends Exception
{
    public function __construct($type, $code = 0, $previous = null)
    {
        $message = "Factory type $type is not supported";
        parent::__construct($message, $code, $previous);
    }
}
