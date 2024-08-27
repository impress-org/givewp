<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\DeleteCampaignPage;
use Give\Campaigns\Migrations\P2P\SetCampaignType;
use Give\Campaigns\Migrations\Tables\AddCampaignTypeColumn;
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
        $this->registerMenus();
        $this->registerActions();
        $this->registerMigrations();        
    }


    /**
     * @unreleased
     */
    private function registerMigrations(): void
    {
        give(MigrationsRegister::class)->addMigrations(
            [
                CreateCampaignsTable::class,
                AddCampaignTypeColumn::class,
                SetCampaignType::class,
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
    private function registerActions(): void
    {
        Hooks::addAction('givewp_campaign_deleted', DeleteCampaignPage::class);
    }

    /**
     * @unreleased
     */
    private function registerMenus()
    {
        Hooks::addAction('admin_menu', CampaignsAdminPage::class, 'addCampaignsSubmenuPage', 999);
    }
}
