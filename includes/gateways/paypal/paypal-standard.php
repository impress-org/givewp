<?php
/**
 * PayPal Standard Gateway
 *
 * @package     Give
 * @since       1.0
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @subpackage  Gateways
 */

use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;

if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Listens for a PayPal IPN requests and then sends to the processing function.
 *
 * @since 1.0
 * @return void
 */
function give_listen_for_paypal_ipn()
{
    // Regular PayPal IPN.
    if (isset($_GET['give-listener']) && 'IPN' === $_GET['give-listener']) {
        /**
         * Fires while verifying PayPal IPN
         *
         * @deprecated
         * @since 1.0
         */
        do_action('give_verify_paypal_ipn');

        give(PayPalStandard::class)->handleIpnNotification();
    }
}

add_action('init', 'give_listen_for_paypal_ipn');

/**
 * Process PayPal IPN Refunds
 *
 * @since 1.0
 * @deprecated
 *
 * @param int $payment_id The payment ID.
 *
 * @param array $data IPN Data
 *
 * @return void
 */
function give_process_paypal_refund($data, $payment_id = 0)
{
    // Collect payment details.
    if (empty($payment_id)) {
        return;
    }

    // Only refund payments once.
    if ('refunded' === get_post_status($payment_id)) {
        return;
    }

    $payment_amount = give_donation_amount($payment_id);
    $refund_amount = $data['payment_gross'] * -1;

    if (number_format((float)$refund_amount, 2) < number_format((float)$payment_amount, 2)) {
        give_insert_payment_note(
            $payment_id,
            sprintf( /* translators: %s: Paypal parent transaction ID */
                __('Partial PayPal refund processed: %s', 'give'),
                $data['parent_txn_id']
            )
        );

        return; // This is a partial refund

    }

    give_insert_payment_note(
        $payment_id,
        sprintf( /* translators: 1: Paypal parent transaction ID 2. Paypal reason code */
            __('PayPal Payment #%1$s Refunded for reason: %2$s', 'give'),
            $data['parent_txn_id'],
            $data['reason_code']
        )
    );
    give_insert_payment_note(
        $payment_id,
        sprintf( /* translators: %s: Paypal transaction ID */
            __('PayPal Refund Transaction ID: %s', 'give'),
            $data['txn_id']
        )
    );
    give_update_payment_status($payment_id, 'refunded');
}

/**
 * Get PayPal Redirect
 *
 * @since 1.0
 *
 * @param bool $ssl_check Is SSL?
 *
 * @return string
 */
function give_get_paypal_redirect($ssl_check = false)
{
    if (is_ssl() || ! $ssl_check) {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }

    // Check the current payment mode
    if (give_is_test_mode()) {
        // Test mode
        $paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';
    } else {
        // Live mode
        $paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';
    }

    return apply_filters('give_paypal_uri', $paypal_uri);
}

/**
 * Set the Page Style for offsite PayPal page.
 *
 * @since 1.0
 * @return string
 */
function give_get_paypal_page_style()
{
    $page_style = trim(give_get_option('paypal_page_style', 'PayPal'));

    return apply_filters('give_paypal_page_style', $page_style);
}

/**
 * PayPal Success Page
 *
 * Shows "Donation Processing" message for PayPal payments that are still pending on site return
 *
 * @since      1.0
 * @since 2.19.6 Get donation id from donor session.
 *
 * @param $content
 *
 * @return string
 */
function give_paypal_success_page_content($content)
{
    $session = give_get_purchase_session();
    $payment_id = give_get_donation_id_by_key($session['purchase_key']);

    $payment = get_post($payment_id);
    if ($payment && 'pending' === $payment->post_status) {
        // Payment is still pending so show processing indicator to fix the race condition.
        ob_start();

        give_get_template_part('payment', 'processing');

        $content = ob_get_clean();
    }

    return $content;
}

add_filter('give_payment_confirm_paypal', 'give_paypal_success_page_content');

/**
 * Given a transaction ID, generate a link to the PayPal transaction ID details
 *
 * @since  1.0
 *
 * @param int $payment_id The payment ID for this transaction
 *
 * @param string $transaction_id The Transaction ID
 *
 * @return string                 A link to the PayPal transaction details
 */
function give_paypal_link_transaction_id($transaction_id, $payment_id)
{
    $paypal_base_url = 'https://history.paypal.com/cgi-bin/webscr?cmd=_history-details-from-hub&id=';
    $transaction_url = '<a href="' . esc_url(
            $paypal_base_url . $transaction_id
        ) . '" target="_blank">' . $transaction_id . '</a>';

    return apply_filters('give_paypal_link_payment_details_transaction_id', $transaction_url);
}

add_filter('give_payment_details_transaction_id-paypal', 'give_paypal_link_transaction_id', 10, 2);


/**
 * Get pending donation note.
 *
 * @since 1.6.3
 *
 * @param $pending_reason
 *
 * @return string
 */
function give_paypal_get_pending_donation_note($pending_reason)
{
    $note = '';

    switch ($pending_reason) {
        case 'echeck':
            $note = __('Payment made via eCheck and will clear automatically in 5-8 days.', 'give');
            break;

        case 'address':
            $note = __(
                'Payment requires a confirmed donor address and must be accepted manually through PayPal.',
                'give'
            );
            break;

        case 'intl':
            $note = __(
                'Payment must be accepted manually through PayPal due to international account regulations.',
                'give'
            );
            break;

        case 'multi-currency':
            $note = __('Payment received in non-shop currency and must be accepted manually through PayPal.', 'give');
            break;

        case 'paymentreview':
        case 'regulatory_review':
            $note = __(
                'Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations.',
                'give'
            );
            break;

        case 'unilateral':
            $note = __('Payment was sent to non-confirmed or non-registered email address.', 'give');
            break;

        case 'upgrade':
            $note = __('PayPal account must be upgraded before this payment can be accepted.', 'give');
            break;

        case 'verify':
            $note = __('PayPal account is not verified. Verify account in order to accept this donation.', 'give');
            break;

        case 'other':
            $note = __('Payment is pending for unknown reasons. Contact PayPal support for assistance.', 'give');
            break;
    } // End switch().

    return apply_filters('give_paypal_get_pending_donation_note', $note);
}

/**
 * Build paypal url
 *
 * @deprecated
 *
 * @param int   $payment_id   Payment ID
 * @param array $payment_data Array of payment data.
 *
 * @return mixed|string
 */
function give_build_paypal_url($payment_id, $payment_data)
{
    // Only send to PayPal if the pending payment is created successfully.
    $listener_url = add_query_arg('give-listener', 'IPN', home_url('index.php'));

    // Get the success url.
    $return_url = add_query_arg(
        [
            'payment-confirmation' => 'paypal',
            'payment-id'           => $payment_id,
        ],
        give_get_success_page_uri()
    );

    // Get the PayPal redirect uri.
    $paypal_redirect = trailingslashit(give_get_paypal_redirect()) . '?';

    // Item name.
    $item_name = give_payment_gateway_item_title($payment_data);

    // Setup PayPal API params.
    $paypal_args = [
        'business'      => give_get_option('paypal_email', false),
        'first_name'    => $payment_data['user_info']['first_name'],
        'last_name'     => $payment_data['user_info']['last_name'],
        'email'         => $payment_data['user_email'],
        'invoice'       => $payment_data['purchase_key'],
        'amount'        => $payment_data['price'],
        'item_name'     => stripslashes($item_name),
        'no_shipping'   => '1',
        'shipping'      => '0',
        'no_note'       => '1',
        'currency_code' => give_get_currency($payment_id, $payment_data),
        'charset'       => get_bloginfo('charset'),
        'custom'        => $payment_id,
        'rm'            => '2',
        'return'        => esc_url_raw( $return_url ),
        'cancel_return' => give_get_failed_transaction_uri(),
        'notify_url'    => $listener_url,
        'page_style'    => give_get_paypal_page_style(),
        'cbt'           => get_bloginfo('name'),
        'bn'            => 'givewp_SP',
    ];

    // Add user address if present.
    if ( ! empty($payment_data['user_info']['address'])) {
        $default_address = [
            'line1'   => '',
            'line2'   => '',
            'city'    => '',
            'state'   => '',
            'zip'     => '',
            'country' => '',
        ];

        $address = wp_parse_args($payment_data['user_info']['address'], $default_address);

        $paypal_args['address1'] = $address['line1'];
        $paypal_args['address2'] = $address['line2'];
        $paypal_args['city'] = $address['city'];
        $paypal_args['state'] = $address['state'];
        $paypal_args['zip'] = $address['zip'];
        $paypal_args['country'] = $address['country'];
    }

    // Donations or regular transactions?
    $paypal_args['cmd'] = give_get_paypal_button_type();

    /**
     * Filter the paypal redirect args.
     *
     * @since 1.8
     *
     * @param array $payment_data Payment Data.
     *
     * @param array $paypal_args PayPal Arguments.
     */
    $paypal_args = apply_filters('give_paypal_redirect_args', $paypal_args, $payment_data);

    // Build query.
    $paypal_redirect .= http_build_query($paypal_args);

    // Fix for some sites that encode the entities.
    $paypal_redirect = str_replace('&amp;', '&', $paypal_redirect);

    return $paypal_redirect;
}


/**
 * Get paypal button type.
 *
 * @since 1.8
 * @return string
 */
function give_get_paypal_button_type()
{
    // paypal_button_type can be donation or standard.
    $paypal_button_type = '_donations';
    if ('standard' === give_get_option('paypal_button_type')) {
        $paypal_button_type = '_xclick';
    }

    return $paypal_button_type;
}

/**
 * Update Purchase key for specific gateway.
 *
 * @since 2.2.4
 *
 * @param string $gateway
 * @param string $purchase_key
 *
 * @param string $custom_purchase_key
 *
 * @return string
 */
function give_paypal_purchase_key($custom_purchase_key, $gateway, $purchase_key)
{
    if ('paypal' === $gateway) {
        $invoice_id_prefix = give_get_option('paypal_invoice_prefix', 'GIVE-');
        $custom_purchase_key = $invoice_id_prefix . $purchase_key;
    }

    return $custom_purchase_key;
}

add_filter('give_donation_purchase_key', 'give_paypal_purchase_key', 10, 3);


/**
 * PayPal Standard Connect button.
 *
 * This uses Stripe's Connect button but swaps the link and logo with PayPal's.
 *
 * @since 2.5.0
 * @return string
 */
function give_paypal_connect_button()
{
    ob_start(); ?>

    <script>
        function onboardedCallback(authCode, sharedId) {
            fetch('/seller-server/login-seller', {
                method: 'POST',
                headers: {
                    'content-type': 'application/json'
                },
                body: JSON.stringify({
                    authCode: authCode,
                    sharedId: sharedId
                })
            }).then(function (res) {
                if (!response.ok) {
                    alert("Something went wrong!");
                }
            });
        }
    </script>
    <a target="_blank" data-paypal-onboard-complete="onboardedCallback" href="<Action-URL>&displayMode=minibrowser"
       data-paypal-button="true">Sign up for PayPal</a>
    <script id="paypal-js"
            src="https://www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js"></script>

    <?php
    return ob_get_clean();

    // Prepare Stripe Connect URL.
    //  $link = admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal-standard' );
    //
    //  return sprintf(
    //      '<a href="%1$s" id="give-paypal-connect"><span>%2$s</span></a>',
    //      esc_url( $link ),
    //      esc_html__( 'Connect to PayPal', 'give' )
    //  );

}
