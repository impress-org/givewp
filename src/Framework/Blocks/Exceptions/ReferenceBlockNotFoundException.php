<?php


namespace Give\Framework\Blocks\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @since 2.10.2
 */
class ReferenceBlockNotFoundException extends Exception
{
    public function __construct($name, $code = 0, Exception $previous = null)
    {
        $message = "Reference block with the name \"$name\" not found - cannot insert new block.";
        parent::__construct($message, $code, $previous);
    }
}
