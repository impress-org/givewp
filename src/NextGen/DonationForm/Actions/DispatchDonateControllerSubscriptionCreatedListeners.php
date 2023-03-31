<?php
namespace Give\NextGen\DonationForm\Actions;

use Give\Donations\Models\Donation;
use Give\NextGen\DonationForm\DataTransferObjects\DonateControllerData;
use Give\NextGen\DonationForm\Listeners\UpdateSubscriptionWithLegacyParentPaymentId;
use Give\Subscriptions\Models\Subscription;

class DispatchDonateControllerSubscriptionCreatedListeners {
    /**
     * @unreleased
     */
    public function __invoke(DonateControllerData $formData, Subscription $subscription, Donation $donation )
    {
        (new UpdateSubscriptionWithLegacyParentPaymentId())($subscription->id, $donation->id);
    }
}