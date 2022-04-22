<?php

namespace Give\Donors;

use Give\Donors\Repositories\DonorRepositoryProxy;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 2.19.6
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('donors', DonorRepositoryProxy::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
    }
}
