<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayRefundable;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\ValueObjects\Money;
use Give\Log\Log;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;

/**
 * Class PayPalCommerce
 *
 * Boots the PayPalCommerce gateway and provides its basic registration properties
 *
 * @since 2.9.0
 */
class PayPalCommerce extends PaymentGateway implements PaymentGatewayRefundable
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
     * @since 4.2.1 updated to use updateOrderFromDonation
     * @since 4.1.0 updated to include 3D Secure validation
     * @since 4.0.0 updated to update and capture payment
     * @since 2.19.0
     *
     * @param  array{payPalOrderId: string|null, payPalAuthorizationId: string|null}  $gatewayData
     * @throws \Exception
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        $payPalOrderId = $gatewayData['payPalOrderId'];

         /** @var Repositories\PayPalOrder $payPalOrderRepository */
        $payPalOrderRepository = give(Repositories\PayPalOrder::class);

        $payPalOrder = $payPalOrderRepository->getApprovedOrder($payPalOrderId);

        if ($payPalOrder->status === 'COMPLETED') {
            $this->validatePayPalOrder($payPalOrder);

            $transactionId = $payPalOrder->purchase_units[0]->payments->captures[0]->id;

        } elseif ($payPalOrder->status === 'APPROVED' || $payPalOrder->status === 'CREATED') {
            $this->validate3dSecure($payPalOrder);

            if ($this->shouldUpdateOrder($donation, $payPalOrder)){
                $payPalOrderRepository->updateOrderFromDonation($payPalOrderId, $donation);
            }

            // ready to capture order, response is the updated PayPal order.
            $response = $payPalOrderRepository->approveOrder($payPalOrderId);

            $this->validatePayPalOrder($response);

            $transactionId  = $response->purchase_units[0]->payments->captures[0]->id;
        } else {
            throw new PaymentGatewayException('PayPal Order status is not found.');
        }

        give()->payment_meta->update_meta(
            $donation->id,
            '_give_order_id',
            $payPalOrderId
        );

        return PaymentComplete::make($transactionId)
            ->setPaymentNotes(
                sprintf(
                    __('Transaction Successful. PayPal Transaction ID: %1$s    PayPal Order ID: %2$s', 'give'),
                    $transactionId,
                    $payPalOrderId
                )
            );
    }

    /**
     * @since 4.5.0 Add Accept Credit Card (Smart Buttons Only) setting.
     * @since 3.0.0 Conditionally add "Transaction Type" setting.
     * @since 2.33.0 Register new payment field type setting.
     * @since 2.27.3 Enable Venmo payment method by default.
     * @since 2.16.2 Add setting "Transaction type".
     */
    public function getOptions()
    {
        /* @var MerchantDetail $merchantDetails */
        $merchantDetails = give(MerchantDetail::class);

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
                'name' => esc_html__('Accept Venmo', 'give'),
                'id' => 'paypal_commerce_accept_venmo',
                'type' => 'radio_inline',
                'desc' => esc_html__(
                    'Displays a button allowing Donors to pay with Venmo (US-only). Donations still come into your PayPal account and are subject to normal PayPal transaction fees.',
                    'give'
                ),
                'default' => 'enabled',
                'options' => [
                    'enabled' => esc_html__('Enabled', 'give'),
                    'disabled' => esc_html__('Disabled', 'give'),
                ],
            ],
            [
                'name' => esc_html__('Payment Field Type', 'give'),
                'desc' => sprintf(
                    esc_html__(
                        '"Auto" provides the most payment options to the donor as possible, based on your account. "Smart Buttons Only" shows only the payment buttons. There is no "Hosted Only" option at this time due to limitations with PayPal\'s hosted fields. Not sure what this means? %1$sRead here%2$s.',
                        'give'
                    ),
                    '<a href="https://docs.givewp.com/paypal-settings" target="_blank">',
                    '</a>'
                ),
                'id' => 'paypal_payment_field_type',
                'type' => 'radio_inline',
                'options' => [
                    'auto' => esc_html__('Auto', 'give'),
                    'smart-buttons' => esc_html__('Smart Buttons Only', 'give'),
                ],
                'default' => 'auto',
            ],
            [
                'name' => esc_html__('Accept Credit Card (Smart Buttons Only)', 'give'),
                'id' => 'paypal_commerce_accept_credit_card',
                'type' => 'radio_inline',
                'desc' => esc_html__(
                    'Displays a button allowing Donors to pay with Credit Card. This option is only available if "Smart Buttons Only" is selected on Payment Field Type.',
                    'give'
                ),
                'default' => 'enabled',
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

        if (Utils::isDonationTransactionTypeSupported($merchantDetails->accountCountry ?: '')) {
            $settings = give_settings_array_insert(
                $settings,
                'paypal_commerce_accept_venmo',
                [
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
                ]
            );
        }

        if ($merchantDetails->accountIsReady) {
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
     * @since 4.0.0
     */
    private function shouldUpdateOrder(Donation $donation, $payPalOrder): bool
    {
        $orderAmount = $payPalOrder->purchase_units[0]->amount->value;
        $orderCurrency = $payPalOrder->purchase_units[0]->amount->currency_code;
        $currentOrderAmount = Money::fromDecimal($orderAmount, $orderCurrency);

        if (!$currentOrderAmount->equals($donation->amount)) {
            Log::error(
                sprintf(
                    'Initial PayPal Order amount does not match donation amount. PayPal Order ID: %s, Donation ID: %s',
                    $payPalOrder->id,
                    $donation->id
                )
            );

            return true;
        }

        return false;
    }

    /**
     * @throws PaymentGatewayException
     */
    private function validatePayPalOrder(object $payPalOrder): void
    {
        $transaction = $payPalOrder->purchase_units[0]->payments->captures[0];

        $errors = property_exists($payPalOrder, 'details') ? $payPalOrder->details[0] : [];

        if (!$transaction) {
            throw new PaymentGatewayException('PayPal Order does not have a transaction.');
        }

        if ($transaction->status === "DECLINED") {
            $errorMessage = sprintf(
                __('PayPal Order has been declined.  Transaction status:: %s', 'give'),
                $transaction->status
            );

            throw new PaymentGatewayException($errorMessage);
        }

        if (!empty($errors)) {
            $errorMessage = sprintf(
                __('PayPal Order has an error: %s', 'give'),
                $errors->issue[0]->description
            );

            throw new PaymentGatewayException($errorMessage);
        }

        $this->validate3dSecure($payPalOrder);
    }

    /**
     * @since 4.1.0
     *
     * @throws PaymentGatewayException
     */
    private function validate3dSecure(object $payPalOrder): void
    {
        // Check if the order is not ready for 3D Secure authentication
        if (isset($payPalOrder->payment_source->card->authentication_result->liability_shift) && !in_array($payPalOrder->payment_source->card->authentication_result->liability_shift, ['POSSIBLE', 'YES'])) {
            throw new PaymentGatewayException('Card type and issuing bank are not ready to complete a 3D Secure authentication.');
        }
    }

    /**
     * @since 4.7.0
     *
     * @throws Exception
     */
    public function refundDonation(Donation $donation): PaymentRefunded
    {
        try {
            $payPalOrderRepository = give(Repositories\PayPalOrder::class);
            $payPalOrderRepository->refundPayment($donation->gatewayTransactionId);

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(
                    __('Donation refunded in PayPal for transaction ID: %s', 'give'),
                    $donation->gatewayTransactionId
                ),
            ]);

            return new PaymentRefunded();
        } catch (Exception $e) {
            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(
                    __(
                        'Error! Donation %s was NOT refunded. Find more details on the error in the logs at Donations > Tools > Logs. To refund the donation, use the PayPal dashboard tools.',
                        'give'
                    ),
                    $donation->id
                ),
            ]);

            throw new PaymentGatewayException(sprintf(__('PayPal API error: %s', 'give'), $e->getMessage()));
        }
    }
}
