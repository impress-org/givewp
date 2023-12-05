<?php

namespace Give\DonationForms\V2;

use Give\DonationForms\V2\ListTable\DonationFormsListTable;
use Give\DonationForms\V2\Repositories\DonationFormsRepository;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 2.19.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('donationForms', DonationFormsRepository::class);
        give()->singleton(DonationFormsListTable::class, function () {
            $listTable = new DonationFormsListTable();
            Hooks::doAction('givewp_donation_forms_list_table', $listTable);

            return $listTable;
        });
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $userId = get_current_user_id();
        $showLegacy = get_user_meta($userId, '_give_donation_forms_archive_show_legacy', true);
        // only register new admin page if user hasn't chosen to use the old one
        if (empty($showLegacy)) {
            Hooks::addAction('admin_menu', DonationFormsAdminPage::class, 'register', 0);
            Hooks::addAction('admin_menu', DonationFormsAdminPage::class, 'highlightAllFormsMenuItem');

            if (DonationFormsAdminPage::isShowing()) {
                Hooks::addAction('admin_enqueue_scripts', DonationFormsAdminPage::class, 'loadScripts');
            }
        } elseif (DonationFormsAdminPage::isShowingLegacyPage()) {
            Hooks::addAction('admin_head', DonationFormsAdminPage::class, 'renderReactSwitch');
        }

        // Onboarding
        Hooks::addAction('submitpost_box', DonationFormsAdminPage::class, 'renderMigrationGuideBox');
        Hooks::addAction('admin_enqueue_scripts', DonationFormsAdminPage::class, 'loadMigrationScripts');

        add_action('wp_ajax_givewp_show_onboarding_banner', static function () {
            add_user_meta(get_current_user_id(), 'givewp-show-onboarding-banner', time(), true);
        });

        add_action('wp_ajax_givewp_show_upgraded_tooltip', static function () {
            add_user_meta(get_current_user_id(), 'givewp-show-upgraded-tooltip', time(), true);
        });
    }
}
