<?php

namespace Give\Framework\ListTableAPI\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @unreleased
 */
class ReferenceColumnNotFoundException extends Exception
{
    public function __construct($id, $code = 0, Exception $previous = null)
    {
        $message = "Reference column with the id \"$id\" not found.";
        parent::__construct($message, $code, $previous);
    }
}
