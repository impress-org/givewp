<?php

namespace Give\NextGen\Gateways\Stripe\NextGenStripeGateway\Webhooks\Listeners;

use Exception;
use Stripe\Event;
use Stripe\Subscription as StripeSubscription;

/**
 * @unreleased
 */
class CustomerSubscriptionCreated
{
    use StripeWebhookListenerRepository;

    /**
     * Processes customer.subscription.created event.
     *
     * Occurs whenever a customer is signed up for a new plan.
     *
     * @see https://stripe.com/docs/api/events/types#event_types-customer.subscription.created
     *
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function __invoke(Event $event)
    {
        try {
            $this->processEvent($event);
        } catch (Exception $exception) {
            $this->logWebhookError($event, $exception);
        }

        exit;
    }

    /**
     * @unreleased
     */
    public function processEvent(Event $event)
    {
        /* @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data->object;

        $subscription = give()->subscriptions->queryByGatewaySubscriptionId($stripeSubscription->id)->get();

        // only use this for next gen for now
        if (!$subscription || !$this->shouldProcessSubscription($subscription)) {
            return;
        }

        // exit early to prevent legacy webhook
        // we don't need to do anything here at the moment because the subscription is already created & active
        exit;
    }
}
