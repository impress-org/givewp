<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\Support\ValueObjects\Money;
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
     * @since 4.1.0 Add PayPal-Partner-Attribution-Id header
     * @since 2.9.0
     *
     * @return object
     * @throws Exception
     * @see        https://developer.paypal.com/docs/api/orders/v2/#orders_capture
     *
     */
    public function approveOrder(string $orderId)
    {
        $request = new OrdersCaptureRequest($orderId);
        $request->headers["PayPal-Partner-Attribution-Id"] = give('PAYPAL_COMMERCE_ATTRIBUTION_ID');

        try {
            return $this->paypalClient->getHttpClient()->execute($request)->result;
        } catch (Exception $exception) {
            PaymentGatewayLog::error(
                'Capture PayPal Commerce payment failure',
                [
                    'response' => $exception->getMessage()
                ]
            );

            throw $exception;
        }
    }

    /**
     * Create order.
     *
     * @see https://developer.paypal.com/docs/api/orders/v2
     *
     * @since 4.1.0 updated to include 3d secure params for card payments
     * @since 3.4.2 Extract the amount parameters to a separate method
     * @since 3.1.0 "payer" argument is deprecated, using payment_source/paypal.
     * @since 2.9.0
     * @since 2.16.2 Conditionally set transaction as donation or standard transaction in PayPal.
     *
     * @throws Exception|HttpException|IOException
     */
    public function createOrder(array $array, string $intent = 'CAPTURE'): string
    {
        $this->validateCreateOrderArguments($array);

        $request = new OrdersCreateRequest();
        $request->payPalPartnerAttributionId(give('PAYPAL_COMMERCE_ATTRIBUTION_ID'));

        $purchaseUnits = array_merge(
            $this->getAmountParameters($array),
            [
                'description' => $array['formTitle'],
                'payee' => [
                    'email_address' => $this->merchantDetails->merchantId,
                    'merchant_id' => $this->merchantDetails->merchantIdInPayPal,
                ],
            ]
        );

        if ($intent === 'CAPTURE') {
            $purchaseUnits = array_merge($purchaseUnits, [
                'payment_instruction' => [
                    'disbursement_mode' => 'INSTANT',
                ]
            ]);
        }

        $requestBody = [
            'intent' => $intent,
            'payment_source' => [
                "paypal" => [
                    'name' => [
                        "given_name" => $array['payer']['firstName'],
                        "surname" => $array['payer']['lastName'],
                    ],
                    "email_address" => $array['payer']['email'],
                ],
                'card' => [
                    'attributes' => [
                        'verification' => [
                            'method' => 'SCA_WHEN_REQUIRED'
                        ]
                    ]
                ]
            ],
            'purchase_units' => [
                $purchaseUnits
            ],
            'application_context' => [
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
            ],
        ];

        if (!empty($array['payer']['address'])){
            $requestBody['payment_source']['paypal']['address'] = $array['payer']['address'];
        }

        $request->body = $requestBody;

        try {
            return $this->paypalClient->getHttpClient()->execute($request)->result->id;
        } catch (Exception $exception) {
            PaymentGatewayLog::error(
                'Create PayPal Commerce order failure',
                [
                    'response' => $exception->getMessage()
                ]
            );

            throw $exception;
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
     * @since 4.1.0 Add PayPal-Partner-Attribution-Id header
     * @since 3.4.2
     *
     * @return mixed
     *
     * @throws Exception|HttpException|IOException
     * @see        https://github.com/paypal/Checkout-PHP-SDK/blob/develop/samples/PatchOrder.php
     *
     */
    public function updateOrderAmount($orderId, array $array)
    {
        $this->validateCreateOrderArguments($array);

        $patchRequest = new OrdersPatchRequest($orderId);

        $patchRequest->headers["PayPal-Partner-Attribution-Id"] = give('PAYPAL_COMMERCE_ATTRIBUTION_ID');

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
     * Update order amount using the Donation model.
     *
     * @since 4.2.1 updated to support donation category
     * @since 4.1.0 Add PayPal-Partner-Attribution-Id header
     * @since 4.0.0
     *
     * @throws Exception|HttpException|IOException
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_patch
     *
     */
    public function updateOrderFromDonation(string $orderId, Donation $donation)
    {
        $patchRequest = new OrdersPatchRequest($orderId);

        $patchRequest->headers["PayPal-Partner-Attribution-Id"] = give('PAYPAL_COMMERCE_ATTRIBUTION_ID');

        $value = $donation->amount->formatToDecimal();
        $currency = $donation->amount->getCurrency()->getCode();
        $name = $donation->formTitle;

        $amountParameters = [
            'amount' => [
                'value' => $value,
                'currency_code' => $currency
            ],
        ];

        if ($this->settings->isTransactionTypeDonation()) {
            $amountParameters['items'] = [
                [
                    'name' => $name,
                    'unit_amount' => [
                        'value' => $value,
                        'currency_code' => $currency,
                    ],
                    'quantity' => 1,
                    'category' => 'DONATION',
                ],
            ];

            $amountParameters['amount']['breakdown'] = [
                'item_total' => [
                    'currency_code' => $currency,
                    'value' => $value,
                ],
            ];
        }

        $patchRequest->body = [
            [
                'op' => 'replace',
                'path' => "/purchase_units/@reference_id=='default'",
                'value' => $amountParameters
            ],
        ];

        try {
            return $this->paypalClient->getHttpClient()->execute($patchRequest)->result->id;
        } catch (Exception $exception) {
            PaymentGatewayLog::error(
                'Update PayPal Commerce order failure',
                [
                    'response' => $exception->getMessage(),
                    'request' => $patchRequest->body
                ]
            );

            throw $exception;
        }
    }

    /**
     * Refunds a processed payment
     *
     * @since 4.1.0 Add PayPal-Partner-Attribution-Id header
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

        $refund->headers["PayPal-Partner-Attribution-Id"] = give('PAYPAL_COMMERCE_ATTRIBUTION_ID');

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
     * @since 4.1.0 Add PayPal-Partner-Attribution-Id header
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
        $orderDetailRequest->headers["PayPal-Partner-Attribution-Id"] = give('PAYPAL_COMMERCE_ATTRIBUTION_ID');
        $orderDetails = (array)$this->paypalClient->getHttpClient()->execute($orderDetailRequest)->result;

        return PayPalOrderModel::fromArray($orderDetails);
    }

    /**
     * Get order details from PayPal commerce.
     *
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_get
     *
     * @since 4.1.0 Add PayPal-Partner-Attribution-Id header
     * @since 4.0.0
     *
     * @throws HttpException | IOException
     */
    public function getApprovedOrder(string $orderId)
    {
        $orderDetailRequest = new OrdersGetRequest($orderId);
        $orderDetailRequest->headers["PayPal-Partner-Attribution-Id"] = give('PAYPAL_COMMERCE_ATTRIBUTION_ID');

        return $this->paypalClient->getHttpClient()->execute($orderDetailRequest)->result;
    }
}
