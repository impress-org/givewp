<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\DeleteCampaignPage;
use Give\Campaigns\Migrations\P2P\SetCampaignType;
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
        //
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->registerMigrations();
        $this->registerActions();
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
                SetCampaignType::class,
                CreateCampaignFormsTable::class,
            ]
        );
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
