<?php

namespace Give\Framework\PaymentGateways\Commands;

/***
 * @since 2.18.0
 */
class RedirectOffsite implements GatewayCommand {
    /**
     * @var string
     */
    public $redirectUrl;

    /**
     * @since 2.18.0
     *
     * @param  string  $redirectUrl
     */
    public function __construct($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }
}
