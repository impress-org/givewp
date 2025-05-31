<?php

namespace Give\API\REST\V3\Routes;

use Give\API\REST\V3\Routes\Campaigns\GetCampaignComments;
use Give\API\REST\V3\Routes\Campaigns\GetCampaignRevenue;
use Give\API\REST\V3\Routes\Campaigns\GetCampaignStatistics;
use Give\API\REST\V3\Routes\Campaigns\RegisterCampaignRoutes;
use Give\API\REST\V3\Routes\Donations\RegisterDonationRoutes;
use Give\API\REST\V3\Routes\Donors\RegisterDonorRoutes;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 4.2.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 4.2.0
     */
    public function register()
    {
    }

    /**
     * @since 4.2.0
     */
    public function boot()
    {
        $this->loadCampaignsRoutes();
        $this->registerDonorRoutes();
        $this->registerDonationRoutes();
    }

    /**
     * @since 4.2.0
     */
    private function loadCampaignsRoutes()
    {
        Hooks::addAction('rest_api_init', RegisterCampaignRoutes::class);
        Hooks::addAction('rest_api_init', GetCampaignStatistics::class, 'registerRoute');
        Hooks::addAction('rest_api_init', GetCampaignRevenue::class, 'registerRoute');
        Hooks::addAction('rest_api_init', GetCampaignComments::class, 'registerRoute');
    }

    /**
     * @unreleased
     */
    private function registerDonorRoutes()
    {
        Hooks::addAction('rest_api_init', RegisterDonorRoutes::class);
    }

    /**
     * @unreleased
     */
    private function registerDonationRoutes()
    {
        Hooks::addAction('rest_api_init', RegisterDonationRoutes::class);
    }
}
