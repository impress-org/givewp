<?php

namespace Give\Campaigns;

use Give\Campaigns\Migrations\Tables\CreateCampaignFormsTable;
use Give\Campaigns\Migrations\Tables\CreateCampaignsTable;
use Give\Framework\Migrations\MigrationsRegister;
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
        $this->registerTableNames();
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function boot(): void
    {
        // Hooks::addAction('init', Actions\MyAction::class);
        // Hooks::addAction('rest_api_init', Controllers\MyEndpoint::class);

        $this->registerMigrations();
        $this->registerMenus();
    }


    /**
     * @unreleased
     */
    private function registerMigrations(): void
    {
        give(MigrationsRegister::class)->addMigrations(
            [
                CreateCampaignsTable::class,
                CreateCampaignFormsTable::class,
            ]
        );
    }


    /**
     * @unreleased
     */
    private function registerTableNames(): void
    {
        global $wpdb;

        $wpdb->give_campaigns = $wpdb->prefix . 'give_campaigns';
        $wpdb->give_campaign_forms = $wpdb->prefix . 'give_campaign_forms';
    }

    /**
     * @unreleased
     */
    private function registerMenus()
    {
        Hooks::addAction('admin_menu', CampaignsAdminPage::class, 'addCampaignsSubmenuPage', 999);
    }
}
