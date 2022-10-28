<?php

namespace Give\NextGen\DonationForm\Routes;

use Give\NextGen\DonationForm\Controllers\DonationFormViewController;
use Give\NextGen\DonationForm\DataTransferObjects\DonationFormViewRouteData;

/**
 * @unreleased
 */
class DonationFormViewRoute
{
    /**
     * @unreleased
     *
     * @return string|void
     */
    public function __invoke()
    {
        // fail silently for use with template_redirect
        if (!$this->isViewValid()) {
            return;
        }

        // create DTO from GET or POST request
        $routeData = DonationFormViewRouteData::fromRequest(give_clean($_REQUEST));

        // let the controller handle the request
        return give(DonationFormViewController::class)->show($routeData);
    }

    /**
     * @unreleased
     */
    private function isViewValid(): bool
    {
        return isset($_REQUEST['givewp-view']) && $_REQUEST['givewp-view'] === 'donation-form';
    }
}
