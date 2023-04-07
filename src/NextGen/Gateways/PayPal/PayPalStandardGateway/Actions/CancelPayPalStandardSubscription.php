<?php

namespace Give\NextGen\Gateways\PayPal\PayPalStandardGateway\Actions;

use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Subscriptions\Models\Subscription;

class CancelPayPalStandardSubscription
{
    /**
     * *PORTED OVER FROM GIVE-RECURRING (give-recurring-paypal.php)*
     *
     * Cancel PayPal Subscription.
     *
     * Performs an Express Checkout NVP API operation as passed in $api_method.
     * Although the PayPal Standard API provides no facility for cancelling a subscription,
     * the PayPal; Express Checkout NVP API can be used.
     *
     * @since 0.3.0
     * @throws PaymentGatewayException
     */
    public function __invoke(Subscription $subscription)
    {
        $credentials = $this->get_paypal_standard_api_credentials();

        // validate credentials
        foreach ($credentials as $cred => $value) {
            if (empty($value)) {
                PaymentGatewayLog::error(
                    '[PayPal Standard]: There was a problem cancelling the subscription.',
                    ['error' => "Missing credential from settings: $cred."]
                );

                throw new PaymentGatewayException(
                    'There was a problem cancelling the subscription, please contact customer support.'
                );
            }
        }

        $username = $credentials['username'];
        $password = $credentials['password'];
        $signature = $credentials['signature'];

        if (give_is_test_mode()) {
            $api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $api_endpoint = 'https://api-3t.paypal.com/nvp';
        }

        $args = array(
            'USER' => $username,
            'PWD' => $password,
            'SIGNATURE' => $signature,
            'METHOD' => 'ManageRecurringPaymentsProfileStatus',
            'PROFILEID' => $subscription->gatewaySubscriptionId,
            'VERSION' => '124',
            'ACTION' => 'Cancel',
        );

        $error_msg = '';
        $request = wp_remote_post($api_endpoint, array(
            'body' => $args,
            'httpversion' => '1.1',
            'timeout' => 30,
        ));

        if (is_wp_error($request)) {
            $success = false;
            $error_msg = $request->get_error_message();
        } else {
            $body = wp_remote_retrieve_body($request);

            if (is_string($body)) {
                wp_parse_str($body, $body);
            }

            if (empty($request['response'])) {
                $success = false;
            }

            if (empty($request['response']['code']) || 200 !== (int)$request['response']['code']) {
                $success = false;
            }

            if (empty($request['response']['message']) || 'OK' !== $request['response']['message']) {
                $success = false;
            }

            if (isset($body['ACK']) && 'success' === strtolower($body['ACK'])) {
                $success = true;
            } elseif (isset($body['L_LONGMESSAGE0'])) {
                $error_msg = $body['L_LONGMESSAGE0'];
            }
        }

        if (empty($success)) {
            PaymentGatewayLog::error(
                '[PayPal Standard]: There was a problem cancelling the subscription.',
                ['error' => $error_msg]
            );

            throw new PaymentGatewayException(
                'There was a problem cancelling the subscription, please contact customer support.'
            );
        }
    }

    /**
     *
     * *PORTED OVER FROM GIVE-RECURRING (give-recurring-paypal.php)*
     *
     * Retrieve PayPal API credentials
     *
     * @access      public
     * @since       1.0
     *
     * @return mixed
     */
    public function get_paypal_standard_api_credentials()
    {
        $prefix = 'live_';

        if (give_is_test_mode()) {
            $prefix = 'test_';
        }

        $creds = array(
            'username' => give_get_option($prefix . 'paypal_standard_api_username'),
            'password' => give_get_option($prefix . 'paypal_standard_api_password'),
            'signature' => give_get_option($prefix . 'paypal_standard_api_signature'),
        );

        return apply_filters('give_recurring_get_paypal_standard_api_credentials', $creds);
    }
}