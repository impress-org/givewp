<?php

namespace Give\Framework\PaymentGateways\Actions;

class GenerateGatewayRouteUrl
{
    /**
     * @param  string  $gatewayId
     * @param  string  $gatewayMethod
     * @param  array|null  $args
     * @return string
     * @since 2.18.0
     *
     * @since 2.19.0 remove $donationId param in favor of args
     *
     */
    public function __invoke($gatewayId, $gatewayMethod, $args = null)
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
