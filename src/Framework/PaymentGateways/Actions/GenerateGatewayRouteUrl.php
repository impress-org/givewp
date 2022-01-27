<?php

namespace Give\Framework\PaymentGateways\Actions;

use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use InvalidArgumentException;

class GenerateGatewayRouteUrl
{
    /**
     * @since 2.18.0
     * @unreleased Return URL with nonce action.
     *
     * @param string $gatewayId
     * @param string $gatewayMethod
     * @param int $donationId
     * @param array|null $additionalQueryArgs
     * @param array|null $optParams
     *
     * @return string Url.
     */
    public function __invoke($gatewayId, $gatewayMethod, $donationId, $additionalQueryArgs = null, $optParams = null)
    {
        $queryArgs = [
            'give-listener' => 'give-gateway',
            'give-gateway-id' => $gatewayId,
            'give-gateway-method' => $gatewayMethod,
            'give-donation-id' => $donationId,
        ];

        if ($additionalQueryArgs) {
            $queryArgs = array_merge($queryArgs, $additionalQueryArgs);
        }

        // Add nonce to url on demand.
        if ( $optParams && array_key_exists('withNonce', $optParams) && $optParams['withNonce']) {
            $queryArgs['_wpnonce'] = wp_create_nonce(
                $this->getNonceActionName($queryArgs)
            );
        }

        return add_query_arg(
            $queryArgs,
            home_url()
        );
    }

    /**
     * Return nonce action name.
     *
     * @unreleased
     *
     * @param array|GatewayRouteData $data
     *
     * @return string
     */
    public function getNonceActionName($data)
    {
        if (is_array($data)) {
            return "{{$data['give-gateway-id']}}-{$data['give-gateway-method']}-{$data['give-donation-id']}";
        }

        if ($data instanceof GatewayRouteData) {
            return "{{$data->gatewayId}}-{$data->gatewayMethod}-{$data->donationId}";
        }

        throw new InvalidArgumentException(
            'This function only accept data in array format or GatewayRouteData class object.'
        );
    }
}
