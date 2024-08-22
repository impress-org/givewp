<?php

namespace Give\Campaigns;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     * @inheritDoc
     */
    public function register(): void
    {
        //
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function boot(): void
    {
        Hooks::addAction('init', Actions\RegisterCampaignPagePostType::class);
        Hooks::addAction('admin_action_edit_campaign_page', Actions\EditCampaignPageRedirect::class);
    }
}
