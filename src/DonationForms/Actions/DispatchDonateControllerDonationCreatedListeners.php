<?php
namespace Give\DonationForms\Actions;

use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\DonationForms\Listeners\AddRedirectUrlsToGatewayData;
use Give\DonationForms\Listeners\StoreCustomFields;
use Give\DonationForms\Listeners\TemporarilyReplaceLegacySuccessPageUri;
use Give\Donations\Models\Donation;

class DispatchDonateControllerDonationCreatedListeners
{
    /**
     * @since 0.3.0
     */
    public function __invoke(DonateControllerData $formData, Donation $donation)
    {
        (new StoreCustomFields())($formData->getDonationForm(), $donation, $formData->getCustomFields());
        (new TemporarilyReplaceLegacySuccessPageUri())($formData, $donation);
        (new AddRedirectUrlsToGatewayData())($formData, $donation);
    }
}