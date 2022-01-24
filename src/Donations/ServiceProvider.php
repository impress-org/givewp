<?php

namespace Give\Donations;

use Give\Donations\Repositories\DonationRepository;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('donationRepository', DonationRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $subscription = give(DonationRepository::class)->getBySubscriptionId(1);

        print_r($subscription);

        exit;

        $donations = give(DonationRepository::class)->getByDonorId(1);

        print_r($donations);
    }
}
