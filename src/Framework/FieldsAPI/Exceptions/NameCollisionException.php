<?php

namespace Give\Framework\FieldsAPI\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\Contracts\Node;

/**
 * @since 2.32.0 add existing and incoming nodes to exception
 * @since 2.10.2
 */
class NameCollisionException extends Exception
{
    /**
     * @var string
     */
    protected $nodeNameCollision;
    /**
     * @var Node
     */
    protected $existingNode;
    /**
     * @var Node
     */
    protected $incomingNode;
    
    public function __construct(
        string $name,
        Node $existingNode,
        Node $incomingNode,
        int $code = 0,
        Exception $previous = null
    )
    {
        $this->nodeNameCollision = $name;
        $this->existingNode = $existingNode;
        $this->incomingNode = $incomingNode;

        $message = "Node name collision for $name";
        parent::__construct($message, $code, $previous);
    }

    /**
     * @since 2.32.0
     */
    public function getNodeNameCollision(): string
    {
        return $this->nodeNameCollision;
    }

    /**
     * @since 2.32.0
     */
    public function getIncomingNode(): Node
    {
        return $this->incomingNode;
    }

    /**
     * @since 2.32.0
     */
    public function getExistingNode(): Node
    {
        return $this->existingNode;
    }
}
