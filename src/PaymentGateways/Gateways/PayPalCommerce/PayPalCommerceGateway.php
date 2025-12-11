<?php

namespace Give\PaymentGateways\Gateways\PayPalCommerce;

use Exception;
use Give\DonationForms\Actions\GenerateDonationFormValidationRouteUrl;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\Helpers\Form\Utils;
use Give\Helpers\Language;
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
     * This function uses to render the credit card form for v2 donation forms.
     *
     * @since 3.0.0
     *
     * @param int $formId
     * @param array $args
     *
     * @return string
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        return parent::getLegacyFormFieldMarkup($formId, $args);
    }

    /**
     * @since 3.1.0 set translations for script
     * @since 3.0.0
     */
    public function enqueueScript(int $formId)
    {
        $assets = $this->getScriptAsset(GIVE_PLUGIN_DIR . 'build/payPalCommerceGateway.asset.php');
        $handle = $this::id();

        wp_enqueue_script(
            $handle,
            GIVE_PLUGIN_URL . 'build/payPalCommerceGateway.js',
            $assets['dependencies'],
            $assets['version'],
            true
        );

        Language::setScriptTranslations($handle);
    }

    /**
     * @since 3.19.1 Add validate URL
     *
     * @throws Exception
     */
    public function formSettings(int $formId): array
    {
        return [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'donationFormId' => $formId,
            'donationFormNonce' => wp_create_nonce("give_donation_form_nonce_{$formId}"),
            'sdkOptions' => $this->getPayPalSDKOptions($formId),
            'validateUrl' => (new GenerateDonationFormValidationRouteUrl())(),
        ];
    }

    /**
     * List of PayPal query parameters: https://developer.paypal.com/docs/checkout/reference/customize-sdk/#query-parameters
     *
     * @since 4.6.0 Removed data-client-token from the SDK options for v3 forms.  For v2 forms, we still need to pass the client token only when hosted fields are enabled.
     * @since 4.5.0 Add support for disabling credit card funding via Smart Buttons Only.
     * @since 3.0.0
     * @throws Exception
     */
    private function getPayPalSDKOptions(int $formId): array
    {
        /* @var MerchantDetail $merchantDetailModel */
        $merchantDetailModel = give(MerchantDetail::class);

        /* @var MerchantDetails $merchantDetailRepository */
        $merchantDetailRepository = give(MerchantDetails::class);

        // Add hosted fields if payment field type is auto and connect account type supports custom payments.
        $paymentFieldType = give_get_option('paypal_payment_field_type', 'auto');
        $acceptCreditCard = give_is_setting_enabled(give_get_option('paypal_commerce_accept_credit_card', 'enabled'));

        $paymentComponents[] = 'buttons';

        $formIsV3 = Utils::isV3Form($formId);
        $venmoEnabled = give_is_setting_enabled(give_get_option('paypal_commerce_accept_venmo', 'disabled'));
        $fieldsEnabled = 'auto' === $paymentFieldType && $merchantDetailModel->supportsCustomPayments;

        $disableFunding = ['credit'];
        if (!$acceptCreditCard && !$fieldsEnabled) {
            $disableFunding[] = 'card';
        }

        $data = [
            'intent' => 'capture',
            'vault' => 'false',
            'currency' => give_get_currency($formId),
        ];

        if ($formIsV3) {
            if ($fieldsEnabled) {
                $paymentComponents[] = 'card-fields';
            }

            $data = array_merge($data, [
                'dataNamespace' => 'givewp/paypal-commerce',
                'clientId' => $merchantDetailModel->clientId,
                'disableFunding' => $disableFunding,
                'dataPartnerAttributionId' => give('PAYPAL_COMMERCE_ATTRIBUTION_ID'),
                'components' => implode(',', $paymentComponents),
            ]);

            if ($venmoEnabled){
                $data['enableFunding'] = 'venmo';
            }
        } else {
            if ($fieldsEnabled) {
                $paymentComponents[] = 'hosted-fields';
                $data['data-client-token'] = $merchantDetailRepository->getClientToken();
            }

            $data = array_merge($data, [
                // data-namespace is required for multiple PayPal SDKs to load in harmony.
                'data-namespace' => 'givewp/paypal-commerce',
                'client-id' => $merchantDetailModel->clientId,
                'disable-funding' => $disableFunding,
                'data-partner-attribution-id' => give('PAYPAL_COMMERCE_ATTRIBUTION_ID'),
                'components' => implode(',', $paymentComponents),
            ]);

            if ($venmoEnabled){
                $data['enable-funding'] = 'venmo';
            }
        }

        return $data;
    }
}
