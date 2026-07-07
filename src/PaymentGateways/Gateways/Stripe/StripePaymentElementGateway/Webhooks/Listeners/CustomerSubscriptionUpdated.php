<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Exception;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Stripe\Event;
use Stripe\Subscription as StripeSubscription;

/**
 * @since TBD
 */
class CustomerSubscriptionUpdated
{
    use StripeWebhookListenerRepository;

    /**
     * Processes customer.subscription.updated event.
     *
     * Occurs whenever a subscription changes (e.g. switching plans, updating quantity, or
     * recovering from a failed payment once the donor updates their payment method).
     *
     * This listener runs on the give_recurring_stripe_processing_customer_subscription_updated
     * hook dispatched by the Give Recurring add-on. For next-gen (Stripe Payment Element)
     * subscriptions it short-circuits the add-on's legacy handling, so it must also cover the
     * legacy paused -> active recovery in addition to the failing -> active recovery.
     *
     * @see https://stripe.com/docs/api/events/types#event_types-customer.subscription.updated
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

        // When Stripe reports the subscription as active but GiveWP still has it as failing or
        // paused, the subscription was recovered (e.g. the donor updated their payment method).
        if (
            $stripeSubscription->status === 'active' &&
            ($subscription->status->isFailing() || $subscription->status->isPaused())
        ) {
            $subscription->status = SubscriptionStatus::ACTIVE();
            $subscription->save();
        }

        return true;
    }
}
