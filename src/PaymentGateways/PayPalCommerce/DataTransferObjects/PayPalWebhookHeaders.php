<?php

namespace Give\PaymentGateways\PayPalCommerce\DataTransferObjects;

use Give\Framework\Exceptions\Primitives\HttpHeaderException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;

class PayPalWebhookHeaders
{
    /**
     * @since 2.9.0
     * @var string
     */
    public $transmissionId;

    /**
     * @since 2.9.0
     * @var string
     */
    public $transmissionTime;

    /**
     * @since 2.9.0
     * @var string
     */
    public $transmissionSig;

    /**
     * @since 2.9.0
     * @var string
     */
    public $certUrl;

    /**
     * @since 2.9.0
     * @var string
     */
    public $authAlgo;

    /**
     * This grabs the headers from the webhook request to be used in the signature verification
     *
     * A strange thing here is that the headers are inconsistent between live and sandbox mode, so this also checks for
     * both forms of the headers (studly case and all caps).
     *
     * @since 4.3.2 Normalize header keys to lowercase and replace underscores with hyphens.
     * @since 2.9.0
     *
     * @param array $headers
     *
     * @return self
     * @throws HttpHeaderException
     */
    public static function fromHeaders(array $headers)
    {
        $normalizedHeaders = [];
        foreach ($headers as $key => $value) {
            $normalizedHeaders[str_replace('_', '-', strtolower($key))] = $value;
        }

        $headerKeys = [
            'transmissionId' => 'paypal-transmission-id',
            'transmissionTime' => 'paypal-transmission-time',
            'transmissionSig' => 'paypal-transmission-sig',
            'certUrl' => 'paypal-cert-url',
            'authAlgo' => 'paypal-auth-algo',
        ];

        $payPalHeaders = new self();
        $missingKeys = [];

        foreach ($headerKeys as $property => $expectedKey) {
            if (isset($normalizedHeaders[$expectedKey])) {
                $payPalHeaders->$property = $normalizedHeaders[$expectedKey];
            } else {
                $missingKeys[] = $expectedKey;
            }
        }

        if ( ! empty($missingKeys)) {
            PaymentGatewayLog::error(
                'Missing PayPal webhook header',
                [
                    'missingKeys' => $missingKeys,
                    'headers' => $headers,
                ]
            );

            throw new HttpHeaderException("Missing PayPal headers: " . implode(', ', $missingKeys));
        }

        return $payPalHeaders;
    }
}
