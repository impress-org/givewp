<?php

namespace Give\Framework\PaymentGateways\Commands;

/***
 * @unreleased
 */
class RespondToBrowser implements GatewayCommand {
    /**
     *
     * @var array
     */
    public $data;

    /**
     * @unreleased
     *
     * @param  array  $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
}