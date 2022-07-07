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
         * @since 2.21.3 fetching event detail in this function can cause of 400 HTTP response for Stripe webhook because
         *             stripe app setup with correct account in event listener class.
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
            $payload = json_decode($payload, true);

            try {
                $event = Event::constructFrom(
                        $payload
                    );
            } catch (\Exception $exception) {
                Log::warning(
                    'Stripe - Webhook Received',
                    [
                        'Payload' => $payload,
                        'Error' => $exception->getMessage()
                    ]
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
    }
}

new Give_Stripe_Webhooks();
