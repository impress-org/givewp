<?php

namespace Give\PaymentGateways\PayPalCommerce\DataTransferObjects;

use Give\Framework\Exceptions\Primitives\HttpHeaderException;

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
     * @since 2.9.0
     *
     * @param array $headers
     *
     * @return self
     * @throws HttpHeaderException
     */
    public static function fromHeaders(array $headers)
    {
        $headerKeys = [
            'transmissionId' => 'Paypal-Transmission-Id',
            'transmissionTime' => 'Paypal-Transmission-Time',
            'transmissionSig' => 'Paypal-Transmission-Sig',
            'certUrl' => 'Paypal-Cert-Url',
            'authAlgo' => 'Paypal-Auth-Algo',
        ];

        $payPalHeaders = new self();
        $missingKeys = [];
        foreach ($headerKeys as $property => $key) {
            if ( ! isset($headers[$key])) {
                $key = strtoupper($key);
            }

            if (isset($headers[$key])) {
                $payPalHeaders->$property = $headers[$key];
            } else {
                $missingKeys[] = $key;
            }
        }

        if ( ! empty($missingKeys)) {
            give_record_gateway_error(
                'Missing PayPal webhook header',
                print_r(
                    [
                        'missingKeys' => $missingKeys,
                        'headers' => $headers,
                    ],
                    true
                )
            );

            throw new HttpHeaderException("Missing PayPal header: $key");
        }

        return $payPalHeaders;
    }
}
