<?php

namespace Give\Framework\FieldsAPI\Contracts;

use JsonSerializable;

interface Node extends JsonSerializable
{
    /**
     * The primitive node type, one of "field", "element", or "group".
     *
     * @since 2.22.0
     */
    public function getNodeType(): string;

    /**
     * Get the field’s type.
     */
    public function getType(): string;

    /**
     * Get the node’s name.
     */
    public function getName(): string;

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize();
}
