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
     * @since TBD Defer the legacy check to admin_menu and register the "Forms" submenu only for users
     *            who have not switched to the legacy list, since the React bundle is only enqueued for it.
     *
     * @inheritDoc
     */
    public function boot()
    {
        add_action('admin_menu', static function () {
            $showLegacy = get_user_meta(get_current_user_id(), '_give_donation_forms_archive_show_legacy', true);

            if ( ! empty($showLegacy)) {
                if (DonationFormsAdminPage::isShowingLegacyPage()) {
                    Hooks::addAction('admin_head', DonationFormsAdminPage::class, 'renderReactSwitch');
                }

                return;
            }

            give(DonationFormsAdminPage::class)->register();

            if (DonationFormsAdminPage::isShowing()) {
                Hooks::addAction('admin_enqueue_scripts', DonationFormsAdminPage::class, 'loadScripts');
            }
        }, 0);

        // Onboarding
        Hooks::addAction('submitpost_box', DonationFormsAdminPage::class, 'renderMigrationGuideBox');
        Hooks::addAction('admin_enqueue_scripts', DonationFormsAdminPage::class, 'loadMigrationScripts');

        // Dismiss notices
        $noticeActions = [
            'givewp_show_onboarding_banner' => 'show-onboarding-banner',
            'givewp_show_upgraded_tooltip' => 'show-upgraded-tooltip',
            'givewp_show_default_form_tooltip' => 'show-default-form-tooltip',
        ];

        foreach ($noticeActions as $action => $metaKey) {
            add_action("wp_ajax_{$action}", static function () use ($metaKey) {
                add_user_meta(get_current_user_id(), "givewp-{$metaKey}", time(), true);
            });
        }
    }
}
