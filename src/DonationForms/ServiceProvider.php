<?php

namespace Give\DonationForms;

use Give\DonationForms\Repositories\DonationFormsRepository;
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
        give()->singleton('donationFormsRepository', DonationFormsRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $userId = get_current_user_id();
        $showLegacy = get_user_meta($userId, '_give_donation_forms_archive_show_legacy', true);
        // only register new admin page if user hasn't chosen to use the old one
        if(empty($showLegacy))
        {
            Hooks::addAction('admin_menu', DonationFormsAdminPage::class, 'register');
            Hooks::addAction('admin_menu', DonationFormsAdminPage::class, 'highlightAllFormsMenuItem');

            if (DonationFormsAdminPage::isShowing()) {
                Hooks::addAction('admin_enqueue_scripts', DonationFormsAdminPage::class, 'loadScripts');
            }
        }
        elseif(DonationFormsAdminPage::isShowingLegacyPage())
        {
            Hooks::addAction( 'admin_head', DonationFormsAdminPage::class, 'renderReactSwitch');
        }
    }
}
