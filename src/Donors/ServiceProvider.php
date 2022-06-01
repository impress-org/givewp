<?php

namespace Give\Donors;

use Give\Donors\Repositories\DonorRepositoryProxy;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 2.19.6
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('donors', DonorRepositoryProxy::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $userId = get_current_user_id();
        $showLegacy = get_user_meta($userId, '_give_donors_archive_show_legacy', true);
        // only register new admin page if user hasn't chosen to use the old one
        if(empty($showLegacy)) {
            Hooks::addAction('admin_menu', DonorsAdminPage::class, 'registerMenuItem');

            if (DonorsAdminPage::isShowing()) {
                Hooks::addAction('admin_enqueue_scripts', DonorsAdminPage::class, 'loadScripts');
            }
        }
        elseif(DonorsAdminPage::isShowing())
        {
            Hooks::addAction( 'admin_head', DonorsAdminPage::class, 'renderReactSwitch');
        }
    }
}
