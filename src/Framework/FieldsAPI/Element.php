<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Node;

/**
 * @since      2.12.0
 * @since      2.13.0 Support visibility conditions
 * @since 2.22.0 Add TapNode trait
 */
abstract class Element implements Node
{
    use Concerns\HasName;
    use Concerns\HasType;
    use Concerns\HasVisibilityConditions;
    use Concerns\SerializeAsJson;
    use Concerns\TapNode;

    /**
     * @since 2.12.0
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getNodeType(): string
    {
        return 'element';
    }

    /**
     * Create a named block.
     *
     * @since 2.12.0
     *
     * @param string $name
     *
     * @return static
     */
    public static function make($name)
    {
        return new static($name);
    }
}
