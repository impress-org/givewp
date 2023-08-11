<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\Collection;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;

/**
 * @since 2.10.2
 */
trait NameCollision
{

    /**
     * @since 2.10.2
     *
     * @throws NameCollisionException
     */
    public function checkNameCollisionDeep(Node $node)
    {
        $this->checkNameCollision($node);
        if ($node instanceof Collection) {
            $node->walk([$this, 'checkNameCollision']);
        }
    }

    /**
     * @since 2.32.0 add existing and incoming nodes to exception
     * @since 2.10.2
     *
     * @throws NameCollisionException
     */
    public function checkNameCollision(Node $node)
    {
        if ($existingNode = $this->getNodeByName($node->getName())) {
            throw new NameCollisionException($node->getName(), $existingNode, $node);
        }
    }
}
