<?php
namespace Give\NextGen\DonationForm\Listeners;

class UpdateSubscriptionWithLegacyParentPaymentId {
    /**
     * @unreleased
     */
    public function __invoke(int $subscriptionId, int $donationId)
    {
        give()->subscriptions->updateLegacyParentPaymentId($subscriptionId, $donationId);
    }
}