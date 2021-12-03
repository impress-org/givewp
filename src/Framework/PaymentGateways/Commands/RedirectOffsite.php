<?php

namespace Give\Framework\PaymentGateways\Commands;

/***
 * @unreleased
 */
class RedirectOffsite implements GatewayCommand {
    /**
     * @var string
     */
    public $redirectUrl;

    /**
     * @unreleased
     *
     * @param  string  $redirectUrl
     */
    public function __construct($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }
}