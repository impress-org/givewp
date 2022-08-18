<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Models\PayPalOrder;

/**
 * Class PayPalCommerce
 *
 * Boots the PayPalCommerce gateway and provides its basic registration properties
 *
 * @since 2.9.0
 */
class PayPalCommerce extends PaymentGateway
{
    /**
     * @deprecated
     *
     * Use getId or id function to access payment gateway id.
     *
     */
    const GATEWAY_ID = 'paypal-commerce';

    /**
     * @since 2.19.0
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        return give(AdvancedCardFields::class)->addCreditCardForm($formId);
    }

    /**
     * @since 2.19.0
     *
     * @return string
     */
    public static function id(): string
    {
        return 'paypal-commerce';
    }

    /**
     * @since 2.19.0
     *
     * @return string
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @since 2.19.0
     *
     * @return string
     */
    public function getName(): string
    {
        return esc_html__('PayPal Donations', 'give');
    }

    /**
     * @since 2.19.0
     *
     * @return string
     */
    public function getPaymentMethodLabel(): string
    {
        return esc_html__('Credit Card', 'give');
    }

    /**
     * @inerhitDoc
     * @since 2.19.0
     *
     * @param array{paypalOrder: PayPalOrder} $gatewayData
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        /** @var PayPalOrder $paypalOrder */
        $paypalOrder = $gatewayData['paypalOrder'];

        $command = PaymentComplete::make($paypalOrder->payment->id)
            ->setPaymentNotes(
                sprintf(
                    __('Transaction Successful. PayPal Transaction ID: %1$s    PayPal Order ID: %2$s', 'give'),
                    $paypalOrder->payment->id,
                    $paypalOrder->id
                )
            );

        give()->payment_meta->update_meta(
            $donation->id,
            '_give_order_id',
            $paypalOrder->id
        );

        return $command;
    }

    /**
     * @since 2.16.2 Add setting "Transaction type".
     */
    public function getOptions()
    {
        $settings = [
            [
                'type' => 'title',
                'id' => 'give_gateway_settings_1',
                'table_html' => false,
            ],
            [
                'id' => 'paypal_commerce_introduction',
                'type' => 'paypal_commerce_introduction',
            ],
            [
                'type' => 'sectionend',
                'id' => 'give_gateway_settings_1',
                'table_html' => false,
            ],
            [
                'type' => 'title',
                'id' => 'give_gateway_settings_2',
            ],
            [
                'name' => esc_html__('Account Country', 'give'),
                'id' => 'paypal_commerce_account_country',
                'type' => 'paypal_commerce_account_country',
            ],
            [
                'name' => esc_html__('Connect With Paypal', 'give'),
                'id' => 'paypal_commerce_account_manger',
                'type' => 'paypal_commerce_account_manger',
            ],
            [
                'name' => esc_html__('Transaction Type', 'give'),
                'desc' => esc_html__(
                    'Nonprofits must verify their status to withdraw donations they receive via PayPal. PayPal users that are not verified nonprofits must demonstrate how their donations will be used, once they raise more than $10,000. By default, GiveWP transactions are sent to PayPal as donations. You may change the transaction type using this option if you feel you may not meet PayPal\'s donation requirements.',
                    'give'
                ),
                'id' => 'paypal_commerce_transaction_type',
                'type' => 'radio_inline',
                'options' => [
                    'donation' => esc_html__('Donation', 'give'),
                    'standard' => esc_html__('Standard Transaction', 'give'),
                ],
                'default' => 'donation',
            ],
            [
                'name' => esc_html__('Accept Venmo', 'give'),
                'id' => 'paypal_commerce_accept_venmo',
                'type' => 'radio_inline',
                'desc' => esc_html__(
                    'Displays a button allowing Donors to pay with Venmo (a PayPal Company). Donations still come into your PayPal account and are subject to normal PayPal transaction fees.',
                    'give'
                ),
                'default' => 'disabled',
                'options' => [
                    'enabled' => esc_html__('Enabled', 'give'),
                    'disabled' => esc_html__('Disabled', 'give'),
                ],
            ],
            [
                'name' => esc_html__('PayPal Donations Gateway Settings Docs Link', 'give'),
                'id' => 'paypal_commerce_gateway_settings_docs_link',
                'url' => esc_url('http://docs.givewp.com/paypal-donations'),
                'title' => esc_html__('PayPal Donations Gateway Settings', 'give'),
                'type' => 'give_docs_link',
            ],
            [
                'type' => 'sectionend',
                'id' => 'give_gateway_settings_2',
            ],
        ];

        if (give(MerchantDetail::class)->accountIsReady) {
            $settings = give_settings_array_insert(
                $settings,
                'paypal_commerce_gateway_settings_docs_link',
                [
                    [
                        'name' => esc_html__('Collect Billing Details', 'give'),
                        'id' => 'paypal_commerce_collect_billing_details',
                        'type' => 'radio_inline',
                        'desc' => esc_html__(
                            'If enabled, required billing address fields are added to PayPal Donations Donation forms. These fields are required to process the transaction when enabled. Billing address details are added to both the donation and donor record in GiveWP.',
                            'give'
                        ),
                        'default' => 'disabled',
                        'options' => [
                            'enabled' => esc_html__('Enabled', 'give'),
                            'disabled' => esc_html__('Disabled', 'give'),
                        ],
                    ],
                ]
            );
        }

        /**
         * filter the settings
         *
         * @since 2.9.6
         */
        return apply_filters('give_get_settings_paypal_commerce', $settings);
    }

    /**
     * @since 2.20.0
     * @inerhitDoc
     * @throws Exception
     */
    public function refundDonation(Donation $donation)
    {
        throw new Exception('Method has not been implemented yet. Please use the legacy method in the meantime.');
    }
}
