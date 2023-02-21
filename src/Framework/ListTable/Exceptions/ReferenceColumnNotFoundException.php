<?php

namespace Give\Framework\ListTable\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @since 2.24.0
 */
class ReferenceColumnNotFoundException extends Exception
{
    public function __construct($id, $code = 0, Exception $previous = null)
    {
        $message = "Reference column with the id \"$id\" not found.";
        parent::__construct($message, $code, $previous);
    }
}
