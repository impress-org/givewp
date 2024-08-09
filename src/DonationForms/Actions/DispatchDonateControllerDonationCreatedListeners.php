<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\DonationForms\Listeners\AddRedirectUrlsToGatewayData;
use Give\DonationForms\Listeners\StoreCustomFields;
use Give\DonationForms\Listeners\TemporarilyReplaceLegacySuccessPageUri;
use Give\DonationForms\Listeners\UpdateDonationLevelId;
use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Subscriptions\Models\Subscription;

class DispatchDonateControllerDonationCreatedListeners
{
    /**
     * @since 3.0.0
     * @throws NameCollisionException|Exception
     */
    public function __invoke(DonateControllerData $formData, Donation $donation, ?Subscription $subscription)
    {
        (new StoreCustomFields())($formData->getDonationForm(), $donation, $subscription, $formData->getCustomFields());
        (new TemporarilyReplaceLegacySuccessPageUri())($formData, $donation);
        (new AddRedirectUrlsToGatewayData())($formData, $donation);
        (new UpdateDonationLevelId())($formData->getDonationForm(), $donation);
    }
}
