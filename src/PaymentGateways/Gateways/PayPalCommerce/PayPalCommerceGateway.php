<?php

namespace Give\PaymentGateways\Gateways\PayPalCommerce;

use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;

/**
 * An extension of the PayPalCommerce gateway in GiveWP that supports the NextGenPaymentGatewayInterface.
 */
class PayPalCommerceGateway extends PayPalCommerce
{
    use HasScriptAssetFile;

    /**
     * @since 0.5.0
     */
    public function enqueueScript(int $formId)
    {
        $assets = $this->getScriptAsset(GIVE_NEXT_GEN_DIR . 'build/payPalCommerceGateway.asset.php');

        wp_enqueue_script(
            self::id(),
            GIVE_NEXT_GEN_URL . 'build/payPalCommerceGateway.js',
            $assets['dependencies'],
            $assets['version'],
            true
        );
    }

    public function formSettings(int $formId): array
    {
        /* @var MerchantDetail $merchant */
        $merchantDetailModel = give(MerchantDetail::class);
        $merchantDetailRepository = give(MerchantDetails::class);

        return [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'donationFormId' => $formId,
            'donationFormNonce' => wp_create_nonce( "give_donation_form_nonce_{$formId}" ),
            'sdkOptions' => [
                // data-namespace is required for multiple PayPal SDKs to load in harmony.
                'data-namespace' => 'givewp/paypal-commerce',
                'client-id' => $merchantDetailModel->clientId,
                'merchant-id' => $merchantDetailModel->merchantIdInPayPal,
                'components' => "buttons,hosted-fields",
                'locale' => get_locale(),
                'disable-funding' => 'credit',
                'enable-funding' => 'venmo',
                'intent' => 'capture',
                'vault' => 'false',
                'data-partner-attribution-id' => give('PAYPAL_COMMERCE_ATTRIBUTION_ID'),
                'data-client-token' => $merchantDetailRepository->getClientToken(),
                'currency' => 'USD',
            ],
        ];
    }
}
