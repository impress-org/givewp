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

        // create DTO from GET request
        $routeData = DonationFormViewRouteData::fromRequest(give_clean($_GET));

        // let the controller handle the request
        return give(DonationFormViewController::class)->show($routeData);
    }

    /**
     * @unreleased
     */
    private function isViewValid(): bool
    {
        return isset($_GET['givewp-view']) && $_GET['givewp-view'] === 'donation-form';
    }
}
