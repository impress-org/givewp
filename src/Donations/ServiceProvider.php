<?php

namespace Give\Donations;

use Give\Donations\Listeners\DonationInserted;
use Give\Donations\Listeners\DonationUpdated;
use Give\Donations\Repositories\DonationRepository;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('donations', DonationRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('give_donation_updated', DonationUpdated::class);
        Hooks::addAction('give_donation_inserted', DonationInserted::class);
    }
}
