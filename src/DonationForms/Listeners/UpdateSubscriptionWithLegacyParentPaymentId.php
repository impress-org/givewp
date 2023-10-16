<?php
namespace Give\DonationForms\Listeners;

class UpdateSubscriptionWithLegacyParentPaymentId
{
    /**
     * @since 3.0.0
     */
    public function __invoke(int $subscriptionId, int $donationId)
    {
        give()->subscriptions->updateLegacyParentPaymentId($subscriptionId, $donationId);
    }
}