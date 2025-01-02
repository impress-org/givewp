<?php

namespace Give\Framework\PaymentGateways\Webhooks;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\Facades\ActionScheduler\AsBackgroundJobs;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class WebhookEvents
{
    /**
     * @var PaymentGateway
     */
    protected $gateway;

    /**
     * @unreleased
     */
    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @unreleased
     *
     * @return int The webhook event ID. Zero if there was an error setting the event.
     */
    public function setDonationStatus(
        DonationStatus $status,
        string $gatewayTransactionId,
        string $message = '',
        $skipRecurringDonations = false
    ): int {
        $hook = "givewp_{$this->gateway::id()}_webhook_event_donation_status_{$status->getValue()}";
        $args = [$gatewayTransactionId, $message, $skipRecurringDonations];
        $group = $this->getGroup();

        return AsBackgroundJobs::enqueueAsyncAction($hook, $args, $group);
    }

    /**
     * @unreleased
     *
     * @return int The webhook event ID. Zero if there was an error setting the event.
     */
    public function setSubscriptionStatus(
        SubscriptionStatus $status,
        string $gatewaySubscriptionId,
        string $message = '',
        bool $initialDonationShouldBeCompleted = false
    ): int {
        $hook = "givewp_{$this->gateway::id()}_webhook_event_subscription_status_{$status->getValue()}";
        $args = [$gatewaySubscriptionId, $message, $initialDonationShouldBeCompleted];
        $group = $this->getGroup();

        return AsBackgroundJobs::enqueueAsyncAction($hook, $args, $group);
    }

    /**
     * @unreleased
     *
     * @return int The webhook event ID. Zero if there was an error setting the event.
     */
    public function setSubscriptionFirstDonation(
        string $gatewayTransactionId,
        string $message = '',
        bool $setSubscriptionActive = true
    ): int {
        $hook = "givewp_{$this->gateway::id()}_webhook_event_subscription_first_donation";
        $args = [$gatewayTransactionId, $message, $setSubscriptionActive];
        $group = $this->getGroup();

        return AsBackgroundJobs::enqueueAsyncAction($hook, $args, $group);
    }

    /**
     * @unreleased
     *
     * @return int The webhook event ID. Zero if there was an error setting the event.
     */
    public function setSubscriptionRenewalDonation(
        string $gatewaySubscriptionId,
        string $gatewayTransactionId,
        string $message = ''
    ): int {
        $hook = "givewp_{$this->gateway::id()}_webhook_event_subscription_renewal_donation";
        $args = [$gatewaySubscriptionId, $gatewayTransactionId, $message];
        $group = $this->getGroup();

        return AsBackgroundJobs::enqueueAsyncAction($hook, $args, $group);
    }

    /**
     * @unreleased
     *
     * @param string $returnFormat OBJECT, ARRAY_A, or ids.
     *
     * @return array
     */
    public function getAll(string $returnFormat = OBJECT): array
    {
        return AsBackgroundJobs::getActionsByGroup($this->getGroup());
    }

    /**
     * @unreleased
     *
     * @return int Total deleted webhook events.
     */
    public function deleteAll(): int
    {
        return AsBackgroundJobs::deleteActionsByGroup($this->getGroup());
    }

    /**
     * @unreleased
     */
    private function getGroup(): string
    {
        return 'givewp-payment-gateway-' . $this->gateway::id();
    }
}
