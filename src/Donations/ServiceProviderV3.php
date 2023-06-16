<?php

namespace Give\Donations;

use Give\Donations\CustomFields\Controllers\DonationDetailsController;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 0.4.0
 */
class ServiceProviderV3 implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        add_action('give_view_donation_details_billing_after', static function ($donationId) {
            echo (new DonationDetailsController())->show($donationId);
        });
    }
}
