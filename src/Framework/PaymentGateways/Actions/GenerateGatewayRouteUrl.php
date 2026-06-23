<?php

namespace Give\Framework\PaymentGateways\Actions;

class GenerateGatewayRouteUrl
{
    /**
     * @unreleased Add GIVEWP_GATEWAY_ROUTE_BASE_URL constant
     * @since      2.19.0 remove $donationId param in favor of args
     * @since      2.18.0
     *
     * @param array|null $args
     * @param string     $gatewayId
     * @param  string  $gatewayMethod
     *
     * @return string
     */
    public function __invoke(string $gatewayId, string $gatewayMethod, array $args = []): string
    {
        $queryArgs = [
            'give-listener' => 'give-gateway',
            'give-gateway-id' => $gatewayId,
            'give-gateway-method' => $gatewayMethod,
        ];

        if ($args) {
            $queryArgs = array_merge($queryArgs, $args);
        }

        return esc_url_raw(add_query_arg(
            $queryArgs,
            defined('GIVEWP_GATEWAY_ROUTE_BASE_URL') ? GIVEWP_GATEWAY_ROUTE_BASE_URL : home_url()
        ));
    }
}
