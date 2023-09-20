<?php

namespace Give\PaymentGateways\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * @since 3.0.0
 */
class CopyV2GatewaysSettingsToV3 extends Migration
{

    /**
     * @inerhitDoc
     */
    public static function id()
    {
        return 'copy-v2-gateways-settings-to-v3';
    }

    /**
     * @inerhitDoc
     */
    public static function timestamp()
    {
        return strtotime('2023-09-11');
    }

    /**
     * @inerhitDoc
     */
    public function run()
    {
        $v3Gateways = give_get_option('gateways_v3', null);
        if (is_null($v3Gateways)) {
            $v2Gateways = give_get_option('gateways', []);
            $v3Gateways = array_intersect_key($v2Gateways, give()->gateways->getPaymentGateways(3));
            give_update_option('gateways_v3', $v3Gateways);
        }

        $v3GatewaysLabels = give_get_option('gateways_label_v3', null);
        if (is_null($v3GatewaysLabels)) {
            $v2GatewaysLabels = give_get_option('gateways_label', []);
            $v3GatewaysLabels = array_intersect_key($v2GatewaysLabels, $v3Gateways);
            give_update_option('gateways_label_v3', $v3GatewaysLabels);
        }

        if (is_null(give_get_option('default_gateway_v3', null))) {
            $v2DefaultGateway = give_get_option('default_gateway', '');
            $v3DefaultGateway = array_key_exists($v2DefaultGateway, $v3Gateways) ? $v2DefaultGateway : current(
                array_keys($v3Gateways)
            );
            give_update_option('default_gateway_v3', $v3DefaultGateway);
        }
    }

    /**
     * @inerhitDoc
     */
    public static function title()
    {
        return 'Copy v2 Gateways Settings to v3';
    }
}
