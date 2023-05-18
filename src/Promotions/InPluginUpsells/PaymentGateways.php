<?php

namespace Give\Promotions\InPluginUpsells;

use Give\Helpers\EnqueueScript;
use Give\Helpers\Utils;

class PaymentGateways
{

    /**
     * Load scripts
     *
     * @since 2.27.1
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2')),
            'apiNonce' => wp_create_nonce('wp_rest'),
        ];

        EnqueueScript::make(
            'give-in-plugin-upsells-payment-gateway',
            'assets/dist/js/payment-gateway.js'
        )
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveSettings', $data)
            ->enqueue();
    }

    /**
     *
     * @since 2.27.1
     *
     */
    public function renderPaymentGatewayRecommendation()
    {
        $isDismissed = get_option('givewp_payment_gateway_fee_recovery_recommendation', false);
        $feeRecoveryIsActive = Utils::isPluginActive('give-fee-recovery/give-fee-recovery.php');

        if ($feeRecoveryIsActive | $isDismissed) {
            return;
        }

        require_once GIVE_PLUGIN_DIR . 'src/Promotions/InPluginUpsells/resources/views/payment-gateway.php';
    }

    /**
     *
     * @since 2.27.1
     *
     */
    public static function isShowing(): bool
    {
        $isGatewaysTab = isset($_GET['tab']) && $_GET['tab'] === 'gateways';
        $isGiveFormsPostType = isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms';

        return $isGiveFormsPostType && $isGatewaysTab;
    }

}
