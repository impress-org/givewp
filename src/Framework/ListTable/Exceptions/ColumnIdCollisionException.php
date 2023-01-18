<?php

namespace Give\Framework\ListTable\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @since 2.24.0
 */
class ColumnIdCollisionException extends Exception
{
    public function __construct($id, $code = 0, Exception $previous = null)
    {
        $message = "Column with id \"$id\" already exist";
        parent::__construct($message, $code, $previous);
    }
}
