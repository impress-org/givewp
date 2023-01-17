<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\Collection;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\FieldsAPI\Exceptions\ReferenceNodeNotFoundException;

/**
 * @since 2.10.2
 */
trait InsertNode
{
    /**
     * @inheritDoc
     *
     * @since 2.10.2
     *
     * @throws ReferenceNodeNotFoundException|NameCollisionException
     */
    public function insertAfter(string $siblingName, Node $node): self
    {
        $this->checkNameCollisionDeep($node);
        $this->insertAfterRecursive($siblingName, $node);

        return $this;
    }

    /**
     * @since 2.10.2
     *
     * @return void
     * @throws ReferenceNodeNotFoundException|NameCollisionException
     */
    protected function insertAfterRecursive(string $siblingName, Node $node)
    {
        $siblingIndex = $this->getNodeIndexByName($siblingName);
        if (null !== $siblingIndex) {
            $this->insert(
                $node,
                $siblingIndex + 1
            );

            return;
        }

        if ($this->nodes) {
            foreach ($this->nodes as $childNode) {
                if ($childNode instanceof Collection) {
                    $childNode->insertAfter($siblingName, $node);
                }
            }

            return;
        }

        throw new ReferenceNodeNotFoundException($siblingName);
    }

    /**
     * @inheritDoc
     *
     * @since 2.10.2
     *
     * @throws ReferenceNodeNotFoundException|NameCollisionException
     */
    public function insertBefore(string $siblingName, Node $node): self
    {
        $this->checkNameCollisionDeep($node);
        $this->insertBeforeRecursive($siblingName, $node);

        return $this;
    }

    /**
     * @since 2.10.2
     *
     * @return void
     * @throws ReferenceNodeNotFoundException|NameCollisionException
     */
    protected function insertBeforeRecursive(string $siblingName, Node $node)
    {
        $siblingIndex = $this->getNodeIndexByName($siblingName);
        if (null !== $siblingIndex) {
            $this->insert(
                $node,
                $siblingIndex - 1
            );

            return;
        }

        if ($this->nodes) {
            foreach ($this->nodes as $childNode) {
                if ($childNode instanceof Collection) {
                    $childNode->insertBefore($siblingName, $node);
                }
            }

            return;
        }

        throw new ReferenceNodeNotFoundException($siblingName);
    }

    /**
     * @since 2.24.0 Make index optional to avoid rebuilding array when appending
     * @since 2.10.2
     *
     * @param int|null $index appends to end if null
     *
     * @throws NameCollisionException
     */
    protected function insert(Node $node, int $index = null)
    {
        $this->checkNameCollisionDeep($node);

        if ($index === null) {
            $this->nodes[] = $node;
        } else {
            array_splice($this->nodes, $index, 0, [$node]);
        }
    }

    /**
     * @inheritdoc
     *
     * @since 2.10.2
     *
     * @throws NameCollisionException
     */
    public function append(Node ...$nodes): self
    {
        foreach ($nodes as $node) {
            $this->insert($node);
        }

        return $this;
    }
}
