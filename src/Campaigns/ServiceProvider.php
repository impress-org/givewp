<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\AddCampaignFormFromRequest;
use Give\Campaigns\Actions\AddNewBadgeToAdminMenuItem;
use Give\Campaigns\Actions\ArchiveCampaignFormsAsDraftStatus;
use Give\Campaigns\Actions\ArchiveCampaignPagesAsDraftStatus;
use Give\Campaigns\Actions\AssociateCampaignPageWithCampaign;
use Give\Campaigns\Actions\CreateCampaignPage;
use Give\Campaigns\Actions\CreateDefaultCampaignForm;
use Give\Campaigns\Actions\FormInheritsCampaignGoal;
use Give\Campaigns\Actions\LoadCampaignOptions;
use Give\Campaigns\Actions\RedirectLegacyCreateFormToCreateCampaign;
use Give\Campaigns\Actions\RenderDonateButton;
use Give\Campaigns\Actions\ReplaceGiveFormsCptLabels;
use Give\Campaigns\Migrations\Donations\AddCampaignId as DonationsAddCampaignId;
use Give\Campaigns\Migrations\MigrateFormsToCampaignForms;
use Give\Campaigns\Migrations\P2P\SetCampaignType;
use Give\Campaigns\Migrations\RevenueTable\AddCampaignID as RevenueTableAddCampaignID;
use Give\Campaigns\Migrations\RevenueTable\AddIndexes;
use Give\Campaigns\Migrations\RevenueTable\AssociateDonationsToCampaign;
use Give\Campaigns\Migrations\Tables\CreateCampaignFormsTable;
use Give\Campaigns\Migrations\Tables\CreateCampaignsTable;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;
use Give\DonationForms\V2\DonationFormsAdminPage;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 4.0.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 4.0.0
     * @inheritDoc
     */
    public function register(): void
    {
        give()->singleton('campaigns', CampaignRepository::class);
        give()->bind(RenderDonateButton::class, function () {
            return new RenderDonateButton(
                new BlockRenderController()
            );
        });
        $this->registerTableNames();
    }

    /**
     * @since 4.0.0
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->registerMenus();
        $this->replaceGiveFormsCptLabels();
        $this->registerActions();
        $this->setupCampaignPages();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerCampaignEntity();
        $this->registerCampaignBlocks();
        $this->setupCampaignForms();
        $this->loadCampaignOptions();
        $this->addNewBadgeToMenu();
    }

    /**
     * @since 4.0.0
     */
    private function registerRoutes()
    {
        Hooks::addAction('rest_api_init', Routes\RegisterCampaignRoutes::class);
        Hooks::addAction('rest_api_init', Routes\GetCampaignsListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\DeleteCampaignListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetCampaignStatistics::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetCampaignRevenue::class, 'registerRoute');
        Hooks::addAction('rest_api_init', Routes\GetCampaignComments::class, 'registerRoute');
    }

    /**
     * @since 4.0.0
     */
    private function registerMigrations(): void
    {
        give(MigrationsRegister::class)->addMigrations(
            [
                CreateCampaignsTable::class,
                SetCampaignType::class,
                CreateCampaignFormsTable::class,
                MigrateFormsToCampaignForms::class,
                RevenueTableAddCampaignID::class,
                AssociateDonationsToCampaign::class,
                AddIndexes::class,
                DonationsAddCampaignId::class,
            ]
        );
    }

    /**
     * @since 4.0.0
     */
    private function registerTableNames(): void
    {
        global $wpdb;

        $wpdb->give_campaigns = $wpdb->prefix . 'give_campaigns';
        $wpdb->give_campaign_forms = $wpdb->prefix . 'give_campaign_forms';
    }

    /**
     * @since 4.0.0
     */
    private function registerActions(): void
    {
        Hooks::addAction('givewp_campaign_updated', ArchiveCampaignFormsAsDraftStatus::class);
        Hooks::addAction('givewp_campaign_updated', ArchiveCampaignPagesAsDraftStatus::class);
        Hooks::addAction('givewp_donation_form_creating', FormInheritsCampaignGoal::class);
        Hooks::addAction('givewp_campaign_page_created', AssociateCampaignPageWithCampaign::class);
        Hooks::addAction('give_form_duplicated', Actions\AssignDuplicatedFormToCampaign::class, '__invoke', 10, 2);

        $noticeActions = [
            'givewp_campaign_interaction_notice',
            'givewp_campaign_existing_user_intro_notice',
        ];

        foreach ($noticeActions as $metaKey) {
            register_meta('user', $metaKey, [
                'type' => 'boolean',
                'show_in_rest' => true,
                'single' => true,
            ]);
        }
        // notices
        $notices = [
            'givewp_campaign_interaction_notice',
            'givewp_campaign_listtable_notice',
            'givewp_campaign_form_notice',
            'givewp_campaign_settings_notice'
        ];

        foreach ($notices as $name) {
            add_action('wp_ajax_' . $name, static function () use ($name) {
                add_user_meta(
                    get_current_user_id(),
                    $name,
                    time(),
                    true
                );
            });
        }
    }

    /**
     * @since 4.0.0
     */
    private function registerMenus()
    {
        Hooks::addAction('admin_menu', CampaignsAdminPage::class, 'addCampaignsSubmenuPage', 999);
    }

    /**
     * @since 4.0.0
     */
    private function replaceGiveFormsCptLabels()
    {
        Hooks::addFilter('give_forms_labels', ReplaceGiveFormsCptLabels::class);
    }

    private function setupCampaignPages()
    {
        Hooks::addAction('enqueue_block_editor_assets', Actions\EnqueueCampaignPageEditorAssets::class);
    }

    /**
     * @since 4.0.0
     */
    private function registerCampaignEntity()
    {
        Hooks::addAction('init', Actions\RegisterCampaignEntity::class);
    }

    /**
     * @since 4.0.0
     */
    private function setupCampaignForms()
    {
        if (CampaignsAdminPage::isShowingDetailsPage()) {
            Hooks::addAction('admin_enqueue_scripts', DonationFormsAdminPage::class, 'loadScripts');
        }

        /**
         * We implemented a feature to load the stats columns ("Goal", "Donations" and "Revenue") using an async approach,
         * so we could prevent a long page load on websites with lots of forms. However, the campaign details page's current
         * "Forms" tab still doesn't support it. Still, it's using the same Form List Table that active the async approach by
         * default, so the line below is necessary to disable it while we still don't have support for async loading on this screen.
         *
         * @see https://github.com/impress-org/givewp/pull/7483
         */
        if (!defined('GIVE_IS_ALL_STATS_COLUMNS_ASYNC_ON_ADMIN_FORM_LIST_VIEWS')) {
            define('GIVE_IS_ALL_STATS_COLUMNS_ASYNC_ON_ADMIN_FORM_LIST_VIEWS', false);
        }

        Hooks::addAction('admin_init', RedirectLegacyCreateFormToCreateCampaign::class);

        Hooks::addAction('save_post_give_forms', AddCampaignFormFromRequest::class, 'optionBasedFormEditor', 10, 3);
        Hooks::addAction('givewp_donation_form_created', AddCampaignFormFromRequest::class, 'visualFormBuilder');
        Hooks::addAction('givewp_campaign_created', CreateDefaultCampaignForm::class);
        Hooks::addAction('givewp_campaign_created', CreateCampaignPage::class);
    }

    /**
     * @since 4.0.0
     */
    private function registerCampaignBlocks()
    {
        register_meta('post',
            CampaignPageMetaKeys::CAMPAIGN_ID,
            [
                'type' => 'integer',
                'description' => 'Campaign ID for GiveWP',
                'single' => true,
                'show_in_rest' => true,
            ]
        );

        Hooks::addAction('rest_api_init', Actions\RegisterCampaignIdRestField::class);
        Hooks::addAction('init', Actions\RegisterCampaignBlocks::class);
        Hooks::addAction('enqueue_block_editor_assets', Actions\RegisterCampaignBlocks::class, 'loadBlockEditorAssets');
    }

    /**
     * @since 4.0.0
     */
    private function loadCampaignOptions()
    {
        Hooks::addAction('init', LoadCampaignOptions::class);
    }

    /**
     * @since 4.0.0
     *
     * @return void
     */
    private function addNewBadgeToMenu(): void
    {
        (new AddNewBadgeToAdminMenuItem())();
    }
}
