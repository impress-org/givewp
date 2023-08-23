<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Exception;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Stripe\Event;
use Stripe\Subscription as StripeSubscription;

/**
 * @since 3.0.0
 */
class CustomerSubscriptionDeleted
{
    use StripeWebhookListenerRepository;

    /**
     * Processes customer.subscription.deleted event.
     *
     * Occurs whenever a customer’s subscription ends.
     *
     * @see https://stripe.com/docs/api/events/types#event_types-customer.subscription.deleted
     *
     * @since 3.0.0
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
     * @since 3.0.0
     * @throws Exception
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

        if (!$subscription->status->isCancelled() && !$subscription->status->isCompleted()) {
            $subscription->status = SubscriptionStatus::COMPLETED();
            $subscription->save();
        }
    }
}
