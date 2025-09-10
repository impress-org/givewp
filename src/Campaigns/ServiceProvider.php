<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\AddCampaignFormFromRequest;
use Give\Campaigns\Actions\AddNewBadgeToAdminMenuItem;
use Give\Campaigns\Actions\ArchiveCampaignFormsAsDraftStatus;
use Give\Campaigns\Actions\ArchiveCampaignPagesAsDraftStatus;
use Give\Campaigns\Actions\AssociateCampaignPageWithCampaign;
use Give\Campaigns\Actions\CacheCampaignData;
use Give\Campaigns\Actions\CreateCampaignPage;
use Give\Campaigns\Actions\CreateDefaultCampaignForm;
use Give\Campaigns\Actions\FormInheritsCampaignGoal;
use Give\Campaigns\Actions\LoadCampaignAdminOptions;
use Give\Campaigns\Actions\PreventDeleteDefaultForm;
use Give\Campaigns\Actions\RedirectLegacyCreateFormToCreateCampaign;
use Give\Campaigns\Actions\ReplaceGiveFormsCptLabels;
use Give\Campaigns\Actions\UnarchiveCampaignFormAsPublishStatus;
use Give\Campaigns\ListTable\Routes\DeleteCampaignListTable;
use Give\Campaigns\ListTable\Routes\GetCampaignsListTable;
use Give\Campaigns\Migrations\CacheCampaignsData;
use Give\Campaigns\Migrations\Donations\AddCampaignId as DonationsAddCampaignId;
use Give\Campaigns\Migrations\MigrateFormsToCampaignForms;
use Give\Campaigns\Migrations\P2P\SetCampaignType;
use Give\Campaigns\Migrations\RevenueTable\AddCampaignID as RevenueTableAddCampaignID;
use Give\Campaigns\Migrations\RevenueTable\AddIndexes;
use Give\Campaigns\Migrations\RevenueTable\AssociateDonationsToCampaign;
use Give\Campaigns\Migrations\Tables\CreateCampaignFormsTable;
use Give\Campaigns\Migrations\Tables\CreateCampaignsTable;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\DonationForms\V2\DonationFormsAdminPage;
use Give\Donations\Models\Donation;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give_Payment;

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
        $this->registerTableNames();
    }

    /**
     * @since 4.8.0 add registerCampaignCache
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
        $this->registerListTableRoutes();
        $this->registerCampaignEntity();
        $this->registerCampaignBlocks();
        $this->setupCampaignForms();
        $this->loadCampaignAdminOptions();
        $this->addNewBadgeToMenu();
        $this->registerCampaignCache();
    }

    /**
     * @since 4.2.0 Move V3 routes to top level API folder and rename method
     * @since 4.0.0
     */
    private function registerListTableRoutes()
    {
        Hooks::addAction('rest_api_init', GetCampaignsListTable::class, 'registerRoute');
        Hooks::addAction('rest_api_init', DeleteCampaignListTable::class, 'registerRoute');
    }

    /**
     * @since 4.8.0 add CacheCampaignData
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
                CacheCampaignsData::class
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
        Hooks::addAction('givewp_campaign_updated', UnarchiveCampaignFormAsPublishStatus::class);
        Hooks::addAction('givewp_campaign_updated', ArchiveCampaignPagesAsDraftStatus::class);
        Hooks::addAction('givewp_donation_form_creating', FormInheritsCampaignGoal::class);
        Hooks::addAction('givewp_campaign_page_created', AssociateCampaignPageWithCampaign::class);
        Hooks::addAction('give_form_duplicated', Actions\AssignDuplicatedFormToCampaign::class, '__invoke', 10, 2);

        Hooks::addAction('before_delete_post', PreventDeleteDefaultForm::class);
        Hooks::addAction('transition_post_status', PreventDeleteDefaultForm::class, 'preventTrashStatusChange', 10, 3);

        $noticeActions = [
            'givewp_campaign_interaction_notice',
            'givewp_campaign_existing_user_intro_notice',
            'givewp_campaign_form_goal_notice',
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
        Hooks::addAction('init', Actions\RegisterCampaignShortcodes::class);
    }

    /**
     * @since 4.6.1 Move to admin_enqueue_scripts hook
     * @since 4.0.0
     */
    private function loadCampaignAdminOptions()
    {
        add_action('admin_enqueue_scripts', function () {
            if (CampaignsAdminPage::isShowingDetailsPage()) {
                give(LoadCampaignAdminOptions::class)();
            }
        });
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

    /**
     * @since 4.8.0
     */
    private function registerCampaignCache(): void
    {
        add_action('givewp_cache_campaign_data', function (int $campaignId) {
            give(CacheCampaignData::class)->handleCache($campaignId);
        });

        Hooks::addAction('give_insert_payment', CacheCampaignData::class, '__invoke', 11, 1);
        Hooks::addAction('give_update_payment_status', CacheCampaignData::class, '__invoke', 11, 1);

        add_action('give_recurring_add_subscription_payment', function (Give_Payment $legacyPayment) {
            give(CacheCampaignData::class)((int)$legacyPayment->ID);
        }, 11, 1);
    }
}
