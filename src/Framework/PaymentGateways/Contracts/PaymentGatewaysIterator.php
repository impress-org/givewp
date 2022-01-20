<?php

namespace Give\Framework\PaymentGateways\Contracts;

/**
 * @since 2.18.0
 * @property-read array $gateways
 */
class PaymentGatewaysIterator implements \Iterator
{
    /**
     * @since 2.18.0
     * @return string
     */
    public function current()
    {
        return current($this->gateways);
    }

    /**
     * @since 2.18.0
     */
    public function next()
    {
        next($this->gateways);
    }

    /**
     * @since 2.18.0
     * @return string
     */
    public function key()
    {
        return key($this->gateways);
    }

    /**
     * @since 2.18.0
     * @return bool
     */
    public function valid()
    {
        return key($this->gateways) !== null;
    }

    /**
     * @since 2.18.0
     */
    public function rewind()
    {
        reset($this->gateways);
    }
}
