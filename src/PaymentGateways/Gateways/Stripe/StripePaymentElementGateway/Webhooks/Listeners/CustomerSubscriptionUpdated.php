<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Exception;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateDonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Stripe\Event;
use Stripe\Subscription as StripeSubscription;

/**
 * @since 4.16.7
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
     * @since 4.16.7
     *
     * @param Event $event
     *
     * @return void
     * @throws Exception
     */
    public function __invoke($event)
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
     * @since 4.20.0 Cancel a subscription that Stripe expired before its first payment.
     * @since 4.16.7
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

        if ($stripeSubscription->status === 'incomplete_expired') {
            $this->handleIncompleteSubscriptionExpired($subscription);

            return true;
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

    /**
     * Fail the unpaid first donation and cancel the subscription after Stripe expired it.
     *
     * Stripe sends no customer.subscription.deleted event for incomplete_expired, so the
     * cancellation happens here. The gateway's cancelSubscription() is not called, because the
     * Stripe subscription can no longer be changed.
     *
     * @since 4.20.0
     *
     * @param Subscription $subscription The GiveWP subscription Stripe expired
     *
     * @throws Exception
     */
    protected function handleIncompleteSubscriptionExpired(Subscription $subscription): void
    {
        $message = __('Stripe expired the subscription because its first payment was not completed.', 'give');

        $initialDonation = $subscription->initialDonation();

        /*
         * The first donation is marked failed before the subscription is cancelled, because the
         * cancellation emails in the Give Recurring add-on check the first donation's status.
         */
        if ($initialDonation && ($initialDonation->status->isPending() || $initialDonation->status->isProcessing())) {
            (new UpdateDonationStatus())($initialDonation, DonationStatus::FAILED(), $message);
        }

        if ($subscription->status->isPending() || $subscription->status->isActive()) {
            (new UpdateSubscriptionStatus())($subscription, SubscriptionStatus::CANCELLED(), $message);
        }
    }
}
