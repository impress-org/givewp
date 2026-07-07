<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Exception;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Stripe\Event;
use Stripe\Subscription as StripeSubscription;

/**
 * @since TBD
 */
class CustomerSubscriptionResumed
{
    use StripeWebhookListenerRepository;

    /**
     * Processes customer.subscription.resumed event.
     *
     * Occurs whenever a subscription is no longer paused. Only applies when a subscription is
     * resumed after being paused, which can follow the donor updating their payment method.
     *
     * @see https://stripe.com/docs/api/events/types#event_types-customer.subscription.resumed
     *
     * @since TBD
     *
     * @return void
     * @throws Exception
     */
    public function __invoke(Event $event)
    {
        try {
            if ($this->processEvent($event)) {
                exit;
            }
        } catch (Exception $exception) {
            $this->logWebhookError($event, $exception);
        }
    }

    /**
     * @since TBD
     * @throws Exception
     */
    public function processEvent(Event $event): bool
    {
        /* @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data->object;

        $subscription = give()->subscriptions->queryByGatewaySubscriptionId($stripeSubscription->id)->get();

        // only use this for next gen for now
        if (!$subscription || !$this->shouldProcessSubscription($subscription)) {
            return false;
        }

        if ($subscription->status->isFailing() || $subscription->status->isPaused()) {
            $subscription->status = SubscriptionStatus::ACTIVE();
            $subscription->save();
        }

        return true;
    }
}
