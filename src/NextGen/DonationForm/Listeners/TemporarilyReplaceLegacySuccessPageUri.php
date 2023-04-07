<?php
namespace Give\NextGen\DonationForm\Listeners;

use Give\Donations\Models\Donation;
use Give\NextGen\DonationForm\DataTransferObjects\DonateControllerData;

class TemporarilyReplaceLegacySuccessPageUri
{
    /**
     * Use our new receipt url for the success page uri.
     *
     * The give_get_success_page_uri() function is used by the legacy gateway processing and is specific to how that form works.
     *
     * In Next Gen, our confirmation receipt page is stateless, and need to use the form request data to generate the url.
     *
     * This is a temporary solution until we can update the gateway api to support the new receipt urls.
     *
     * @since 0.3.0 move logic to an action
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function __invoke(DonateControllerData $formData, Donation $donation)
    {
        $filteredUrl = $formData->getDonationConfirmationReceiptViewRouteUrl($donation);

        add_filter('give_get_success_page_uri', static function ($url) use ($filteredUrl) {
            return $filteredUrl;
        });
    }
}