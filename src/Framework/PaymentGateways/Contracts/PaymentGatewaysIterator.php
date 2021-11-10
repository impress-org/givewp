<?php

namespace Give\Framework\PaymentGateways\Contracts;

/**
 * @unreleased
 * @property-read array $gateways
 */
class PaymentGatewaysIterator implements \Iterator
{
    /**
     * @unreleased
     * @return string
     */
    public function current()
    {
        return current($this->gateways);
    }

    /**
     * @unreleased
     */
    public function next()
    {
        next($this->gateways);
    }

    /**
     * @unreleased
     * @return string
     */
    public function key()
    {
        return key($this->gateways);
    }

    /**
     * @unreleased
     * @return bool
     */
    public function valid()
    {
        return key($this->gateways) !== null;
    }

    /**
     * @unreleased
     */
    public function rewind()
    {
        reset($this->gateways);
    }
}
