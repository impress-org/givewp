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
    use Concerns\SerializeAsJson;
    use Concerns\TapNode;
    use Concerns\HasVisibilityConditions {
        Concerns\HasVisibilityConditions::__construct as private __visibilityConditionsConstruct;
    }

    /**
     * @since      2.12.0
     * @since 2.23.1 Make constructor final to avoid unsafe usage of `new static()`.
     *
     * @param string $name
     */
    final public function __construct($name)
    {
        $this->name = $name;

        $this->__visibilityConditionsConstruct();
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
