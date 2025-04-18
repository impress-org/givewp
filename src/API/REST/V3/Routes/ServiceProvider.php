<?php

namespace Give\API\REST\V3\Routes;

use Give\API\REST\V3\Routes\Campaigns\GetCampaignComments;
use Give\API\REST\V3\Routes\Campaigns\GetCampaignRevenue;
use Give\API\REST\V3\Routes\Campaigns\GetCampaignStatistics;
use Give\API\REST\V3\Routes\Campaigns\RegisterCampaignRoutes;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     */
    public function register()
    {
        // TODO: Implement register() method.
    }

    /**
     * @unreleased
     */
    public function boot()
    {
        $this->loadCampaignsRoutes();
    }

    /**
     * @unreleased
     */
    private function loadCampaignsRoutes()
    {
        Hooks::addAction('rest_api_init', RegisterCampaignRoutes::class);
        Hooks::addAction('rest_api_init', GetCampaignStatistics::class, 'registerRoute');
        Hooks::addAction('rest_api_init', GetCampaignRevenue::class, 'registerRoute');
        Hooks::addAction('rest_api_init', GetCampaignComments::class, 'registerRoute');
    }
}
