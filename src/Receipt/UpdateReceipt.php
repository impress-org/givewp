<?php

namespace Give\Receipt;

/**
 * Abstract class UpdateReceipt
 *
 * @package Give\Receipt
 * @since 2.7.0
 */
abstract class UpdateReceipt
{
    /**
     * @var Receipt
     */
    protected $receipt;

    /**
     * UpdateReceipt constructor.
     *
     * @since 2.7.0
     *
     * @param $receipt
     */
    public function __construct($receipt)
    {
        $this->receipt = $receipt;
    }

    abstract public function apply();
}
