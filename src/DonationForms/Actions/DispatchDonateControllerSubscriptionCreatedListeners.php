<?php
namespace Give\DonationForms\Actions;

use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\DonationForms\Listeners\UpdateSubscriptionWithLegacyParentPaymentId;
use Give\Donations\Models\Donation;
use Give\Subscriptions\Models\Subscription;

class DispatchDonateControllerSubscriptionCreatedListeners
{
    /**
     * @since 3.0.0
     */
    public function __invoke(DonateControllerData $formData, Subscription $subscription, Donation $donation)
    {
        (new UpdateSubscriptionWithLegacyParentPaymentId())($subscription->id, $donation->id);
    }
}