<?php

namespace Give\Framework\PaymentGateways\Actions;

class GenerateReturnUrlFromRedirectOffsite {
    /**
     * @param  string  $gatewayId
     * @param  string  $gatewayMethod
     * @param  array  $args
     * @return string
     */
    public function __invoke($gatewayId, $gatewayMethod, $args)
    {
        $queryArgs = [
            'give-listener' => 'give-gateway',
            'give-gateway-id' => $gatewayId,
            'give-gateway-method' => $gatewayMethod,
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