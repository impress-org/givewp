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
     * @since 3.0.0
     */
    public function enqueueScript(int $formId)
    {
        $assets = $this->getScriptAsset(GIVE_PLUGIN_DIR . 'build/payPalCommerceGateway.asset.php');

        wp_enqueue_script(
            self::id(),
            GIVE_PLUGIN_URL . 'build/payPalCommerceGateway.js',
            $assets['dependencies'],
            $assets['version'],
            true
        );
    }

    /**
     * @throws \Exception
     */
    public function formSettings(int $formId): array
    {
        return [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'donationFormId' => $formId,
            'donationFormNonce' => wp_create_nonce("give_donation_form_nonce_{$formId}"),
            'sdkOptions' => $this->getPayPalSDKOptions($formId),
        ];
    }

    /**
     * List of PayPal query parameters: https://developer.paypal.com/docs/checkout/reference/customize-sdk/#query-parameters
     *
     * @unreleased
     * @throws \Exception
     */
    private function getPayPalSDKOptions(int $formId): array
    {
        /* @var MerchantDetail $merchantDetailModel */
        $merchantDetailModel = give(MerchantDetail::class);

        /* @var MerchantDetails $merchantDetailRepository */
        $merchantDetailRepository = give(MerchantDetails::class);

        // Add hosted fields if payment field type is auto.
        $paymentFieldType = give_get_option('paypal_payment_field_type', 'auto');
        $paymentComponents[] = 'buttons';
        if ('auto' === $paymentFieldType) {
            $paymentComponents[] = 'hosted-fields';
        }

        $data = [
            // data-namespace is required for multiple PayPal SDKs to load in harmony.
            'data-namespace' => 'givewp/paypal-commerce',
            'client-id' => $merchantDetailModel->clientId,
            'merchant-id' => $merchantDetailModel->merchantIdInPayPal,
            'components' => implode(',', $paymentComponents),
            'disable-funding' => 'credit',
            'intent' => 'capture',
            'vault' => 'false',
            'data-partner-attribution-id' => give('PAYPAL_COMMERCE_ATTRIBUTION_ID'),
            'data-client-token' => $merchantDetailRepository->getClientToken(),
            'currency' => give_get_currency($formId),
        ];

        if (give_is_setting_enabled(give_get_option('paypal_commerce_accept_venmo', 'disabled'))) {
            $data['enable-funding'] = 'venmo';
        }

        return $data;
    }
}
