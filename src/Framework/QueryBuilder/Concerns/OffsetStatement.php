<?php

namespace Give\Framework\QueryBuilder\Concerns;

/**
 * @unreleased
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
