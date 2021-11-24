<?php

namespace Give\Framework\PaymentGateways\Actions;

class GenerateReturnUrlFromRedirectOffsite {
    /**
     * @param  string  $gatewayId
     * @param  string  $gatewayMethod
     * @param  int  $paymentId
     * @param  array|null  $args
     * @return string
     */
    public function __invoke($gatewayId, $gatewayMethod, $paymentId, $args = null)
    {
        $queryArgs = [
            'give-listener' => 'give-gateway',
            'give-gateway-id' => $gatewayId,
            'give-gateway-method' => $gatewayMethod,
            'give-payment-id' => $paymentId,
        ];

        if ($args) {
            $queryArgs = array_merge($queryArgs, $args);
        }

        return add_query_arg(
            $queryArgs,
            home_url()
        );
    }
}