<?php
namespace Give\NextGen\DonationForm\Actions;

use Give\Donations\Models\Donation;
use Give\NextGen\DonationForm\DataTransferObjects\DonateControllerData;
use Give\NextGen\DonationForm\Listeners\AddRedirectUrlsToGatewayData;
use Give\NextGen\DonationForm\Listeners\StoreCustomFields;
use Give\NextGen\DonationForm\Listeners\TemporarilyReplaceLegacySuccessPageUri;

class DispatchDonateControllerDonationCreatedListeners {
    /**
     * @unreleased
     */
    public function __invoke(DonateControllerData $formData, Donation $donation)
    {
        (new StoreCustomFields())($formData->getDonationForm(), $donation, $formData->getCustomFields());
        (new TemporarilyReplaceLegacySuccessPageUri())($formData, $donation);
        (new AddRedirectUrlsToGatewayData())($formData, $donation);
    }
}