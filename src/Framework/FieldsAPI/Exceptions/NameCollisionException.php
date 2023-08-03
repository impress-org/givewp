<?php

namespace Give\Framework\FieldsAPI\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @since 2.10.2
 */
class NameCollisionException extends Exception
{
    /**
     * @var string
     */
    protected $nodeNameCollision;
    
    public function __construct($name, $code = 0, Exception $previous = null)
    {
        $this->nodeNameCollision = $name;

        $message = "Node name collision for $name";
        parent::__construct($message, $code, $previous);
    }

    /**
     * @unreleased
     */
    public function getNodeNameCollision(): string
    {
        return $this->nodeNameCollision;
    }
}
