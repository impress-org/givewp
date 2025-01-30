<?php

namespace Give\PaymentGateways\Gateways\PayPalCommerce;

use Exception;
use Give\DonationForms\Actions\GenerateDonationFormValidationRouteUrl;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\Helpers\Language;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;

/**
 * An extension of the PayPalCommerce gateway in GiveWP that supports the NextGenPaymentGatewayInterface.
 */
class PayPalCommerceGateway extends PayPalCommerce
{
    public $routeMethods = [
        'createOrder',
        'authorizeOrder',
    ];

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
            'createOrderUrl'=> $this->generateGatewayRouteUrl('createOrder', [
                'formId' => $formId,
            ]),
            'authorizeOrderUrl'=> $this->generateGatewayRouteUrl('authorizeOrder', [
                'formId' => $formId,
            ]),
            'donationFormId' => $formId,
            'donationFormNonce' => wp_create_nonce("give_donation_form_nonce_{$formId}"),
            'nonce' => wp_create_nonce('givewp_paypal_commerce_gateway'),
            'sdkOptions' => $this->getPayPalSDKOptions($formId),
            'validateUrl' => (new GenerateDonationFormValidationRouteUrl())(),
        ];
    }

    /**
     * List of PayPal query parameters: https://developer.paypal.com/docs/checkout/reference/customize-sdk/#query-parameters
     *
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
        $paymentComponents[] = 'buttons';
        if ('auto' === $paymentFieldType && $merchantDetailModel->supportsCustomPayments) {
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

    /**
     * @unreleased
     */
    public function createOrder(array $queryParams)
    {
        check_ajax_referer( 'givewp_paypal_commerce_gateway' );

        $data = give_clean($_POST);

        $args = [
            'formTitle' => $data['formTitle'],
            'formId' => $data['formId'],
            'donationAmount' => $data['donationAmount'],
            'payer' => [
                'firstName' => $data['firstName'],
                'lastName' => $data['lastName'],
                'email' => $data['email'],
            ],
        ];

        /** @var PayPalOrder $payPalOrder */
        $payPalOrder = give(PayPalOrder::class);

        try {
            $orderId = $payPalOrder->createOrder($args, 'AUTHORIZE');

            wp_send_json_success(
                [
                    'id' => $orderId,
                ]
            );
        } catch (\Exception $ex) {
            wp_send_json_error(
                [
                    'error' => json_decode($ex->getMessage(), true),
                ]
            );
        }
    }

    public function authorizeOrder(array $queryParams)
    {
        check_ajax_referer( 'givewp_paypal_commerce_gateway' );

        $data = give_clean($_POST);

        /** @var PayPalOrder $payPalOrder */
        $payPalOrder = give(PayPalOrder::class);

        try {
            $authorizationId = $payPalOrder->authorizeOrder($data['orderId']);

            wp_send_json_success(
                [
                    'id' => $authorizationId,
                ]
            );
        } catch (\Exception $ex) {
            wp_send_json_error(
                [
                    'error' => json_decode($ex->getMessage(), true),
                ]
            );
        }
    }
}
