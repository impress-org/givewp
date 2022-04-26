<?php
/**
 * Give - Stripe Core | Process Webhooks
 *
 * @package    Give
 * @since 2.5.0
 *
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

use Give\Log\Log;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners\ChargeRefunded;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners\CheckoutSessionCompleted;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners\PaymentIntentPaymentFailed;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners\PaymentIntentSucceeded;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\StripeEventListener;
use Stripe\Event;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Give_Stripe_Webhooks')) {
    /**
     * Class Give_Stripe_Webhooks
     *
     * @since 2.5.0
     */
    class Give_Stripe_Webhooks
    {

        /**
         * Stripe Gateway
         *
         * @since  2.5.0
         * @access public
         *
         * @var $stripe_gateway
         */
        public $stripe_gateway;

        /**
         * Give_Stripe_Webhooks constructor.
         *
         * @since 2.5.0
         */
        public function __construct()
        {
            $this->stripe_gateway = new Give_Stripe_Gateway();

            add_action('init', [$this, 'listen']);
        }

        /**
         * Listen for Stripe events.
         *
         * @access public
         * @since  2.5.0
         *
         * @return void
         */
        public function listen()
        {
            $give_listener = give_clean(filter_input(INPUT_GET, 'give-listener'));

            // Must be a stripe listener to proceed.
            if ('stripe' !== $give_listener) {
                return;
            }

            // Retrieve the request's body and parse it as JSON.
            $payload = @file_get_contents('php://input');

            try {
                $event = Event::constructFrom(
                    json_decode($payload, true)
                );
            } catch (\UnexpectedValueException $e) {
                Log::warning(
                    'Stripe - Webhook Received',
                    ['Payload' => $payload,]
                );

                status_header(400);
                exit();
            }

            try {
                $processed_event = $this->process($event);

                $message = $processed_event ?
                    sprintf(
                    /* translators: 1. Processing result. */
                        __('Processed event: %s', 'give'),
                        $processed_event
                    ) :
                    __('Something went wrong with processing the payment gateway event.', 'give');
            } catch (\Exception $e) {
                $message = sprintf(
                    'Unable to process %1$s event on %2$s. Error: %3$s',
                    $event->type,
                    home_url(),
                    $e->getMessage()
                );
            }

            status_header(200);
            exit($message);
        }

        /**
         * Process Stripe Webhooks.
         *
         * @since  2.5.0
         * @access public
         *
         * @return string
         * @throws \Give\Framework\Exceptions\Primitives\Exception
         */
        public function process(Event $event)
        {
            $eventListeners = [
                'checkout.session.completed' => CheckoutSessionCompleted::class,
                'payment_intent.succeeded' => PaymentIntentSucceeded::class,
                'payment_intent.payment_failed' => PaymentIntentPaymentFailed::class,
                'charge.refunded' => ChargeRefunded::class
            ];

            if (array_key_exists($event->type, $eventListeners)) {
                /* @var StripeEventListener $stripeEvent */
                $stripeEvent = give($eventListeners[$event->type]);
                $stripeEvent($event);
            }

            do_action('give_stripe_event_' . $event->type, $event);

            return $event->type;
        }

        /**
         * This function will process `checkout.session.completed` webhook event.
         *
         * @since  2.5.5
         * @access public
         *
         * @param Event $event Stripe Event.
         *
         * @return void
         */
        public function process_checkout_session_completed($event)
        {
            // Get Payment Intent data from Event.
            $checkout_session = $event->data->object;

            // Process when Payment Intent status is succeeded.
            $donation_id = give_get_purchase_id_by_transaction_id($checkout_session->id);

            // Update payment status to donation.
            give_update_payment_status($donation_id, 'publish');

            // Insert donation note to inform admin that charge succeeded.
            give_insert_payment_note($donation_id, __('Charge succeeded in Stripe.', 'give'));

            /**
             * This action hook will be used to extend processing the payment intent succeeded event.
             *
             * @since 2.5.5
             */
            do_action('give_stripe_process_checkout_session_completed', $donation_id, $event);
        }

        /**
         * This function will process `payment_intent.succeeded` webhook event.
         *
         * @since  2.5.5
         * @access public
         *
         * @param Event $event Stripe Event.
         *
         * @return void
         */
        public function process_payment_intent_succeeded($event)
        {
            // Get Payment Intent data from Event.
            $intent = $event->data->object;

            // Process when Payment Intent status is succeeded.
            if ('succeeded' === $intent->status) {
                $donation_id = give_get_purchase_id_by_transaction_id($intent->id);

                // Update payment status to donation.
                give_update_payment_status($donation_id, 'publish');

                // Insert donation note to inform admin that charge succeeded.
                give_insert_payment_note($donation_id, __('Charge succeeded in Stripe.', 'give'));
            }

            /**
             * This action hook will be used to extend processing the payment intent succeeded event.
             *
             * @since 2.5.5
             */
            do_action('give_stripe_process_payment_intent_succeeded', $event);
        }

        /**
         * This function will process `payment_intent.failed` webhook event.
         *
         * @since  2.5.5
         * @access public
         *
         * @param Event $event Stripe Event.
         *
         * @return void
         */
        public function process_payment_intent_failed($event)
        {
            // Get Payment Intent data from Event.
            $intent = $event->data->object;
            $donation_id = give_get_purchase_id_by_transaction_id($intent->id);

            // Update payment status to donation.
            give_update_payment_status($donation_id, 'failed');

            // Insert donation note to inform admin that charge succeeded.
            give_insert_payment_note($donation_id, __('Charge failed in Stripe.', 'give'));

            /**
             * This action hook will be used to extend processing the payment intent failed event.
             *
             * @since 2.5.5
             */
            do_action('give_stripe_process_payment_intent_failed', $event);
        }

        /**
         * This function will process `charge.refunded` webhook event.
         *
         * @since  2.5.5
         * @access public
         *
         * @param Event $event Stripe Event.
         *
         * @return void
         */
        public function process_charge_refunded($event)
        {
            global $wpdb;

            $charge = $event->data->object;

            if ($charge->refunded) {
                $payment_id = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT donation_id FROM {$wpdb->donationmeta} WHERE meta_key = '_give_payment_transaction_id' AND meta_value = %s LIMIT 1",
                        $charge->id
                    )
                );

                if ($payment_id) {
                    give_update_payment_status($payment_id, 'refunded');
                    give_insert_payment_note($payment_id, __('Charge refunded in Stripe.', 'give'));
                }
            }

            /**
             * This action hook will be used to extend processing the charge refunded event.
             *
             * @since 2.5.5
             */
            do_action('give_stripe_process_charge_refunded', $event);
        }
    }
}

new Give_Stripe_Webhooks();
