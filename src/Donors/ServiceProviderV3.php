<?php

namespace Give\Donors;

use Give\Donors\CustomFields\Controllers\DonorDetailsController;
use Give\Donors\Models\Donor;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give_Donor as LegacyDonor;

/**
 * @unreleased
 */
class ServiceProviderV3 implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        add_action('give_donor_after_tables', static function (LegacyDonor $legacyDonor) {
            /** @var Donor $donor */
            $donor = Donor::find($legacyDonor->id);

            echo (new DonorDetailsController())->show($donor);
        });
    }
}
