<?php

namespace Give\PaymentGateways\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

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
        $v2Gateways = give_get_option('gateways', []);
        give_update_option('gateways_v3', $v2Gateways);
        give_update_option('gateways_label_v3', give_get_option('gateways_label'));
        give_update_option('default_gateway_v3', give_get_option('default_gateway', current(array_keys($v2Gateways))));
    }

    /**
     * @inerhitDoc
     */
    public static function title()
    {
        return 'Copy v2 Gateways Settings to v3';
    }
}
