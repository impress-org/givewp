<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\Collection;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Field;

trait HasNodes
{
    /**
     * @var Node[]
     */
    protected $nodes = [];

    /**
     * @inheritdoc
     */
    public function getNodeIndexByName(string $name)
    {
        foreach ($this->nodes as $index => $node) {
            if ($node->getName() === $name) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     *
     * @return Node|null
     */
    public function getNodeByName(string $name)
    {
        foreach ($this->nodes as $node) {
            if ($node->getName() === $name) {
                return $node;
            }
            if ($node instanceof Collection) {
                $nestedNode = $node->getNodeByName($name);
                if ($nestedNode !== null) {
                    return $nestedNode;
                }
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function all(): array
    {
        return $this->nodes;
    }

    /**
     * @inheritdoc
     *
     * @return Field[]
     */
    public function getFields(): array
    {
        $fields = [];

        foreach ($this->nodes as $node) {
            if ($node instanceof Field) {
                $fields[] = $node;
            } elseif ($node instanceof Collection) {
                $nestedFields = $node->getFields();

                foreach($nestedFields as $field) {
                    $fields[] = $field;
                }
            }
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->nodes);
    }

}
