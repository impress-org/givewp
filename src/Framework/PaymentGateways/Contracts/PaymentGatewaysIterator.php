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
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->gateways);
    }

    /**
     * @since 2.18.0
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        next($this->gateways);
    }

    /**
     * @since 2.18.0
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->gateways);
    }

    /**
     * @since 2.18.0
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return key($this->gateways) !== null;
    }

    /**
     * @since 2.18.0
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->gateways);
    }
}
