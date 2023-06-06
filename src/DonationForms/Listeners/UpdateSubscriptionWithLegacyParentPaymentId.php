<?php
namespace Give\DonationForms\Listeners;

class UpdateSubscriptionWithLegacyParentPaymentId
{
    /**
     * @since 0.3.0
     */
    public function __invoke(int $subscriptionId, int $donationId)
    {
        give()->subscriptions->updateLegacyParentPaymentId($subscriptionId, $donationId);
    }
}