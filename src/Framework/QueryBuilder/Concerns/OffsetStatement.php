<?php

namespace Give\Framework\QueryBuilder\Concerns;

/**
 * @since 2.19.0
 */
trait OffsetStatement
{
    /**
     * @var int
     */
    protected $offset;

    /**
     * @param  int  $offset
     *
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = (int)$offset;

        return $this;
    }

    protected function getOffsetSQL()
    {
        return $this->limit && $this->offset
            ? ["OFFSET {$this->offset}"]
            : [];
    }
}
