<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Node;

/**
 * @since 2.12.0
 * @since 2.13.0 Support visibility conditions
 */
abstract class Element implements Node
{

    use Concerns\HasName;
    use Concerns\HasType;
    use Concerns\HasVisibilityConditions;
    use Concerns\SerializeAsJson;

    /**
     * @since      2.12.0
     * @unreleased Make constructor as final to avoid unsafe usage of `new static()`.
     *
     * @param string $name
     */
    final public function __construct($name)
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
