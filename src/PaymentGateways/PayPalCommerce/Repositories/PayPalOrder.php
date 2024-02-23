<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Models\PayPalOrder as PayPalOrderModel;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Orders\OrdersPatchRequest;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;
use PayPalHttp\HttpException;
use PayPalHttp\IOException;

use function give_record_gateway_error as logError;

/**
 * Class PayPalOrder
 * @package Give\PaymentGateways\PayPalCommerce\Repositories
 *
 * @since 2.9.0
 */
class PayPalOrder
{
    /**
     * @since 2.9.0
     *
     * @var PayPalClient
     */
    private $paypalClient;

    /**
     * @since 2.9.0
     *
     * @var MerchantDetail
     */
    private $merchantDetails;

    /**
     * @since 2.16.2
     * @var Settings
     */
    private $settings;

    /**
     * PayPalOrder constructor.
     *
     * @since 2.9.0
     * @since 2.16.2 Add third param "Settings" to function
     *
     * @param PayPalClient $paypalClient
     * @param MerchantDetail $merchantDetails
     * @param Settings $settings
     */
    public function __construct(PayPalClient $paypalClient, MerchantDetail $merchantDetails, Settings $settings)
    {
        $this->paypalClient = $paypalClient;
        $this->merchantDetails = $merchantDetails;
        $this->settings = $settings;
    }

    /**
     * Approve order.
     *
     * @since 2.9.0
     *
     * @param string $orderId
     *
     * @return \stdClass
     * @throws Exception
     */
    public function approveOrder($orderId)
    {
        $request = new OrdersCaptureRequest($orderId);

        try {
            return $this->paypalClient->getHttpClient()->execute($request)->result;
        } catch (Exception $ex) {
            logError(
                'Capture PayPal Commerce payment failure',
                sprintf(
                    '<strong>Response</strong><pre>%1$s</pre>',
                    print_r(json_decode($ex->getMessage(), true), true)
                )
            );

            throw $ex;
        }
    }

    /**
     * Create order.
     *
     * @see https://developer.paypal.com/docs/api/orders/v2
     *
     * @since 3.4.2 Extract the amount parameters to a separate method
     * @since 3.1.0 "payer" argument is deprecated, using payment_source/paypal.
     * @since 2.9.0
     * @since 2.16.2 Conditionally set transaction as donation or standard transaction in PayPal.
     *
     * @throws Exception|HttpException|IOException
     */
    public function createOrder(array $array): string
    {
        $this->validateCreateOrderArguments($array);

        $request = new OrdersCreateRequest();
        $request->payPalPartnerAttributionId(give('PAYPAL_COMMERCE_ATTRIBUTION_ID'));

        $request->body = [
            'intent' => 'CAPTURE',
            'payment_source' => [
                "paypal" => [
                    'name' => [
                        "given_name" => $array['payer']['firstName'],
                        "surname" => $array['payer']['lastName'],
                    ],
                    "email_address" => $array['payer']['email'],
                ],
            ],
            'purchase_units' => [
                array_merge(
                    $this->getAmountParameters($array),
                    [
                        'description' => $array['formTitle'],
                        'payee' => [
                            'email_address' => $this->merchantDetails->merchantId,
                            'merchant_id' => $this->merchantDetails->merchantIdInPayPal,
                        ],
                        'payment_instruction' => [
                            'disbursement_mode' => 'INSTANT',
                        ],
                    ]
                ),
            ],
            'application_context' => [
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
            ],
        ];

        if (! empty($array['payer']['address'])) {
            $request->body['payment_source']['paypal']['address'] = $array['payer']['address'];
        }

        try {
            return $this->paypalClient->getHttpClient()->execute($request)->result->id;
        } catch (Exception $ex) {
            logError(
                'Create PayPal Commerce order failure',
                sprintf(
                    '<strong>Request</strong><pre>%1$s</pre><br><strong>Response</strong><pre>%2$s</pre>',
                    print_r($request->body, true),
                    print_r(json_decode($ex->getMessage(), true), true)
                )
            );

            throw $ex;
        }
    }

    /**
     * @since 3.4.2
     */
    private function getAmountParameters($array): array
    {
        $formId = (int)$array['formId'];
        $donationCurrency = give_get_currency($formId);
        $donationAmount = give_maybe_sanitize_amount(
            $array['donationAmount'],
            ['currency' => give_get_currency($formId)]
        );

        /**
         * To make an update, you must provide a reference_id. If you OMIT THIS VALUE WITH AN ORDER THAT CONTAINS
         * ONLY ONE PURCHASE UNIT, PayPal sets the value to default which enables you to use the path:
         * "/purchase_units/@reference_id=='default'/{attribute-or-object}".
         *
         * @see https://developer.paypal.com/docs/api/orders/v2/#orders_patch
         */
        $amountParameters = [
            //'reference_id' => get_post_field('post_name', $formId),
            'amount' => [
                'value' => $donationAmount,
                'currency_code' => $donationCurrency,
            ],
        ];

        // Set PayPal transaction as donation.
        if ($this->settings->isTransactionTypeDonation()) {
            $amountParameters['items'] = [
                [
                    'name' => get_post_field('post_name', $formId),
                    'unit_amount' => [
                        'value' => $donationAmount,
                        'currency_code' => $donationCurrency,
                    ],
                    'quantity' => 1,
                    'category' => 'DONATION',
                ],
            ];

            $amountParameters['amount']['breakdown'] = [
                'item_total' => [
                    'currency_code' => $donationCurrency,
                    'value' => $donationAmount,
                ],
            ];
        }

        return $amountParameters;
    }

    /**
     * @since 3.4.2
     *
     * @see https://github.com/paypal/Checkout-PHP-SDK/blob/develop/samples/PatchOrder.php
     *
     * @return mixed
     *
     * @throws Exception|HttpException|IOException
     */
    public function updateOrderAmount($orderId, array $array)
    {
        $this->validateCreateOrderArguments($array);

        $patchRequest = new OrdersPatchRequest($orderId);

        $patchRequest->body = [
            0 => [
                'op' => 'replace',
                'path' => '/intent',
                'value' => 'CAPTURE',
            ],
            1 => [
                'op' => 'replace',
                'path' => "/purchase_units/@reference_id=='default'",
                'value' => $this->getAmountParameters($array),
            ],
        ];

        try {
            return $this->paypalClient->getHttpClient()->execute($patchRequest)->result->id;
        } catch (Exception $ex) {
            logError(
                'Update PayPal Commerce order failure',
                sprintf(
                    '<strong>Request</strong><pre>%1$s</pre><br><strong>Response</strong><pre>%2$s</pre>',
                    print_r($patchRequest->body, true),
                    print_r(json_decode($ex->getMessage(), true), true)
                )
            );

            throw $ex;
        }
    }

    /**
     * Refunds a processed payment
     *
     * @since 2.9.0
     *
     * @param $captureId
     *
     * @return string The id of the refund
     * @throws Exception
     */
    public function refundPayment($captureId)
    {
        $refund = new CapturesRefundRequest($captureId);

        try {
            return $this->paypalClient->getHttpClient()->execute($refund)->result->id;
        } catch (Exception $exception) {
            logError(
                'Create PayPal Commerce payment refund failure',
                sprintf(
                    '<strong>Response</strong><pre>%1$s</pre>',
                    print_r(json_decode($exception->getMessage(), true), true)
                )
            );

            throw $exception;
        }
    }

    /**
     * Validate argument given to create PayPal order.
     *
     * @since 2.9.0
     *
     * @param array $array
     *
     * @throws InvalidArgumentException
     */
    private function validateCreateOrderArguments($array)
    {
        $required = ['formId', 'donationAmount', 'payer'];
        $array = array_filter($array); // Remove empty values.

        if (array_diff($required, array_keys($array))) {
            throw new InvalidArgumentException(
                __(
                    'To create a paypal order, please provide formId, donationAmount and payer',
                    'give'
                )
            );
        }
    }

    /**
     * Get order details from paypal commerce.
     *
     * @since 2.19.0
     *
     * @param string $orderId
     *
     * @return PayPalOrderModel
     * @throws HttpException | IOException
     */
    public function getOrder($orderId)
    {
        $orderDetailRequest = new OrdersGetRequest($orderId);
        $orderDetails = (array)$this->paypalClient->getHttpClient()->execute($orderDetailRequest)->result;

        return PayPalOrderModel::fromArray($orderDetails);
    }
}
