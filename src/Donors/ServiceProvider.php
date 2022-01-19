<?php

namespace Give\Donors;

use Give\Donors\Repositories\DonorRepository;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('donorRepository',DonorRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
    }
}
