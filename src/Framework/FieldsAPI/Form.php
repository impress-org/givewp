<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Collection;
use Give\Framework\FieldsAPI\Contracts\Node;

/**
 * @since 2.12.0
 */
class Form implements Node, Collection
{

    use Concerns\HasLabel;
    use Concerns\HasName;
    use Concerns\HasNodes;
    use Concerns\HasType;
    use Concerns\InsertNode;
    use Concerns\MoveNode;
    use Concerns\NameCollision;
    use Concerns\RemoveNode;
    use Concerns\SerializeAsJson;
    use Concerns\WalkNodes;

    const TYPE = 'form';

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @since 2.14.0
     */
    public static function make($name)
    {
        return new static($name);
    }
}
