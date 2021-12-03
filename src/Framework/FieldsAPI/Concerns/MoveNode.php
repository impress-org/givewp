<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait MoveNode
{

    public function move($name)
    {
        $collection = $this;
        $proxy = new MoveNodeProxy($collection);
        $proxy->move(
            $collection->getNodeByName($name)
        );

        return $proxy;
    }
}
