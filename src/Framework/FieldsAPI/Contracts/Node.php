<?php

namespace Give\Framework\FieldsAPI\Contracts;

use JsonSerializable;

interface Node extends JsonSerializable
{
    /**
     * The primitive node type, one of "field", "element", or "group".
     *
     * @unreleased
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
    public function jsonSerialize();
}
