<?php

namespace Give\Framework\PaymentGateways\Commands;

/***
 * @since 2.18.0
 */
class RespondToBrowser implements GatewayCommand {
    /**
     *
     * @var array
     */
    public $data;

    /**
     * @since 2.18.0
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
