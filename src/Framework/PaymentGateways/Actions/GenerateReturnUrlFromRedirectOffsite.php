<?php

namespace Give\Framework\PaymentGateways\Actions;

class GenerateReturnUrlFromRedirectOffsite {
    /**
     * @param string $gatewayId
     * @param string $gatewayMethod
     * @return string
     */
    public function __invoke($gatewayId, $gatewayMethod)
    {
        return add_query_arg(
            [
                'give-listener' => 'give-gateway',
                'give-gateway-id' => $gatewayId,
                'give-gateway-method' => $gatewayMethod
            ],
            home_url()
        );
    }
}