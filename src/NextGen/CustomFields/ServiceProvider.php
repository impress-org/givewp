<?php

namespace Give\NextGen\CustomFields;

use Give\Framework\FieldsAPI\Field;
use Give\Helpers\Hooks;
use Give\NextGen\CustomFields\Controllers\DonationDetailsController;
use Give\NextGen\CustomFields\Controllers\DonorDetailsController;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        Field::macro('shouldDisplayInAdmin', function() {
            return isset($this->displayInAdmin) && $this->displayInAdmin;
        });

        Field::macro('shouldDisplayInReceipt', function() {
            return isset($this->displayInReceipt) && $this->displayInReceipt;
        });
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('give_donor_after_tables', DonorDetailsController::class, 'show');
        Hooks::addAction('give_view_donation_details_billing_after', DonationDetailsController::class, 'show');
    }
}
