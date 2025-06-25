<?php

namespace Give\Framework\PaymentGateways\Webhooks;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\Facades\ActionScheduler\AsBackgroundJobs;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class WebhookEvents
{
    /**
     * @var string
     */
    protected $gatewayId;

    /**
     * @unreleased
     */
    public function __construct(string $gatewayId)
    {
        $this->gatewayId = $gatewayId;
    }

    /**
     * @unreleased
     */
    public function donationAbandoned(
        string $gatewayTransactionId,
        string $message = '',
        $skipRecurringDonations = false
    ) {
        $this->setDonationStatus(DonationStatus::ABANDONED(), $gatewayTransactionId, $message, $skipRecurringDonations);
    }

    /**
     * @unreleased
     */
    public function donationCancelled(
        string $gatewayTransactionId,
        string $message = '',
        $skipRecurringDonations = false
    ) {
        $this->setDonationStatus(DonationStatus::CANCELLED(), $gatewayTransactionId, $message, $skipRecurringDonations);
    }

    /**
     * @unreleased
     */
    public function donationCompleted(
        string $gatewayTransactionId,
        string $message = '',
        $skipRecurringDonations = false
    ) {
        $this->setDonationStatus(DonationStatus::COMPLETE(), $gatewayTransactionId, $message, $skipRecurringDonations);
    }

    /**
     * @unreleased
     */
    public function donationFailed(string $gatewayTransactionId, string $message = '', $skipRecurringDonations = false)
    {
        $this->setDonationStatus(DonationStatus::FAILED(), $gatewayTransactionId, $message, $skipRecurringDonations);
    }

    /**
     * @unreleased
     */
    public function donationPending(string $gatewayTransactionId, string $message = '', $skipRecurringDonations = false)
    {
        $this->setDonationStatus(DonationStatus::PENDING(), $gatewayTransactionId, $message, $skipRecurringDonations);
    }

    /**
     * @unreleased
     */
    public function donationPreapproval(
        string $gatewayTransactionId,
        string $message = '',
        $skipRecurringDonations = false
    ) {
        $this->setDonationStatus(DonationStatus::PREAPPROVAL(), $gatewayTransactionId, $message,
            $skipRecurringDonations);
    }

    /**
     * @unreleased
     */
    public function donationProcessing(
        string $gatewayTransactionId,
        string $message = '',
        $skipRecurringDonations = false
    )
    {
        $this->setDonationStatus(DonationStatus::PROCESSING(), $gatewayTransactionId, $message,
            $skipRecurringDonations);
    }

    /**
     * @unreleased
     */
    public function donationRefunded(string $gatewayTransactionId, string $message = '', $skipRecurringDonations = false)
    {
        $this->setDonationStatus(DonationStatus::REFUNDED(), $gatewayTransactionId, $message, $skipRecurringDonations);
    }

    /**
     * @unreleased
     */
    public function donationRevoked(string $gatewayTransactionId, string $message = '', $skipRecurringDonations = false)
    {
        $this->setDonationStatus(DonationStatus::REVOKED(), $gatewayTransactionId, $message, $skipRecurringDonations);
    }

    /**
     * @unreleased
     */
    public function subscriptionActive(
        string $gatewaySubscriptionId,
        string $message = '',
        bool $initialDonationShouldBeCompleted = false
    )
    {
        $this->setSubscriptionStatus(SubscriptionStatus::ACTIVE(), $gatewaySubscriptionId, $message,
            $initialDonationShouldBeCompleted);
    }

    /**
     * @unreleased
     */
    public function subscriptionCancelled(string $gatewaySubscriptionId, string $message = '')
    {
        $this->setSubscriptionStatus(SubscriptionStatus::CANCELLED(), $gatewaySubscriptionId, $message);
    }

    /**
     * @unreleased
     */
    public function subscriptionCompleted(string $gatewaySubscriptionId, string $message = '')
    {
        $this->setSubscriptionStatus(SubscriptionStatus::COMPLETED(), $gatewaySubscriptionId, $message);
    }

    /**
     * @unreleased
     */
    public function subscriptionExpired(string $gatewaySubscriptionId, string $message = '')
    {
        $this->setSubscriptionStatus(SubscriptionStatus::EXPIRED(), $gatewaySubscriptionId, $message);
    }

    /**
     * @unreleased
     */
    public function subscriptionFailing(string $gatewaySubscriptionId, string $message = '')
    {
        $this->setSubscriptionStatus(SubscriptionStatus::FAILING(), $gatewaySubscriptionId, $message);
    }

    /**
     * @unreleased
     */
    public function subscriptionPaused(string $gatewaySubscriptionId, string $message = '')
    {
        $this->setSubscriptionStatus(SubscriptionStatus::PAUSED(), $gatewaySubscriptionId, $message);
    }

    /**
     * @unreleased
     */
    public function subscriptionPending(string $gatewaySubscriptionId, string $message = '')
    {
        $this->setSubscriptionStatus(SubscriptionStatus::PENDING(), $gatewaySubscriptionId, $message);
    }

    /**
     * @unreleased
     */
    public function subscriptionSuspended(string $gatewaySubscriptionId, string $message = '')
    {
        $this->setSubscriptionStatus(SubscriptionStatus::SUSPENDED(), $gatewaySubscriptionId, $message);
    }

    /**
     * @unreleased
     *
     * @return int The webhook event ID. Zero if there was an error setting the event.
     */
    public function subscriptionFirstDonation(
        string $gatewayTransactionId,
        string $message = '',
        bool $setSubscriptionActive = true,
        bool $setDonationComplete = true, 
        string $gatewaySubscriptionId = ''
    ): int {
        $hook = sprintf('givewp_%s_webhook_event_subscription_first_donation', $this->gatewayId);
        $args = [$gatewayTransactionId, $message, $setSubscriptionActive, $setDonationComplete, $gatewaySubscriptionId];
        $group = $this->getGroup();

        return AsBackgroundJobs::enqueueAsyncAction($hook, $args, $group);
    }

    /**
     * @unreleased
     *
     * @return int The webhook event ID. Zero if there was an error setting the event.
     */
    public function subscriptionRenewalDonation(
        string $gatewaySubscriptionId,
        string $gatewayTransactionId,
        string $message = ''
    ): int {
        $hook = sprintf('givewp_%s_webhook_event_subscription_renewal_donation', $this->gatewayId);
        $args = [$gatewaySubscriptionId, $gatewayTransactionId, $message];
        $group = $this->getGroup();

        return AsBackgroundJobs::enqueueAsyncAction($hook, $args, $group);
    }

    /**
     * @unreleased
     *
     * @return int The webhook event ID. Zero if there was an error setting the event.
     */
    protected function setDonationStatus(
        DonationStatus $status,
        string $gatewayTransactionId,
        string $message = '',
        $skipRecurringDonations = false
    ): int {
        $hook = sprintf('givewp_%s_webhook_event_donation_status_%s', $this->gatewayId, $status->getValue());
        $args = [$gatewayTransactionId, $message, $skipRecurringDonations];
        $group = $this->getGroup();

        return AsBackgroundJobs::enqueueAsyncAction($hook, $args, $group);
    }

    /**
     * @unreleased
     *
     * @return int The webhook event ID. Zero if there was an error setting the event.
     */
    protected function setSubscriptionStatus(
        SubscriptionStatus $status,
        string $gatewaySubscriptionId,
        string $message = '',
        bool $initialDonationShouldBeCompleted = false
    ): int {
        $hook = sprintf('givewp_%s_webhook_event_subscription_status_%s', $this->gatewayId, $status->getValue());
        $args = [$gatewaySubscriptionId, $message];

        if ($initialDonationShouldBeCompleted) {
            $args[] = $initialDonationShouldBeCompleted;
        }

        $group = $this->getGroup();

        return AsBackgroundJobs::enqueueAsyncAction($hook, $args, $group);
    }

    /**
     * @unreleased
     */
    protected function getGroup(): string
    {
        return 'givewp-payment-gateway-' . $this->gatewayId;
    }
}
