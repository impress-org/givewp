<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\Collection;

trait RemoveNode
{
    /**
     * @since 3.0.0 preserve array numeric keys
     * @since 2.10.2
     *
     * @return static
     */
    public function remove(string $name)
    {
        foreach ($this->nodes as $index => $node) {
            if ($node->getName() === $name) {
                unset($this->nodes[$index]);
                $this->nodes = array_values($this->nodes);

                return $this;
            }
            if ($node instanceof Collection) {
                return $node->remove($name);
            }
        }

        // Maybe need to throw an exception if no node is removed.
        return $this;
    }
}
