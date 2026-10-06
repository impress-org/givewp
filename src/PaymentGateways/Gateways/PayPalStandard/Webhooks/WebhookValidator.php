<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Webhooks;

use Give\Log\Log;

/**
 * This class use to validate PayPal Standard ipn.
 * Validate the IPN: https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNImplementation/
 *
 * @since 2.19.0
 */
class WebhookValidator
{
    /**
     * @since 2.19.0
     * @since 2.19.3 Update log message.
     * @since TBD Always validate the IPN with PayPal and verify its SSL certificate.
     *
     * @param array $eventData PayPal ipn body data.
     *
     * @return bool
     */
    public function verifyEventSignature(array $eventData)
    {
        $eventData = array_merge( [ 'cmd' => '_notify-validate' ], $eventData );

        $requestArgs = [
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => [
                'host' => give_is_test_mode() ? 'www.sandbox.paypal.com' : 'www.paypal.com',
                'connection' => 'close',
                'content-type' => 'application/x-www-form-urlencoded',
                'post' => '/cgi-bin/webscr HTTP/1.1',
            ],
            'body' => $eventData,
        ];

        $apiResponse = wp_remote_post(give_get_paypal_redirect(), $requestArgs);

        if (is_wp_error($apiResponse)) {
            Log::error(
                'PayPal Standard IPN Error',
                ['IPN Data' => $apiResponse]
            );

            return false;
        }

        if ('VERIFIED' !== $apiResponse['body']) {
            Log::warning(
                'PayPal Standard IPN Error',
                [
                    'Message' => 'This is not a verified IPN.',
                    'IPN Data' => $apiResponse
                ]
            );

            return false;
        }

        return true;
    }
}
