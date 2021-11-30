<?php

namespace Give\Framework\PaymentGateways\Actions;

class GenerateGatewayRouteUrl
{
    /**
     * @unreleased
     *
     * @param  string  $gatewayId
     * @param  string  $gatewayMethod
     * @param  int  $donationId
     * @param  array|null  $args
     * @return string
     */
    public function __invoke($gatewayId, $gatewayMethod, $donationId, $args = null)
    {
        $queryArgs = [
            'give-listener' => 'give-gateway',
            'give-gateway-id' => $gatewayId,
            'give-gateway-method' => $gatewayMethod,
            'give-donation-id' => $donationId,
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