<?php

declare(strict_types=1);

namespace Give\VendorOverrides\AdminNotices;

use Give\ServiceProviders\ServiceProvider;
use Give\Vendors\StellarWP\AdminNotices\AdminNotices;

/**
 * Registers and boots the Admin Notices library
 *
 * @unreleased
 *
 * @see https://github.com/stellarwp/admin-notices
 */
class AdminNoticesServiceProvider implements ServiceProvider {
    /**
     * {@inheritDoc}
     *
     * @unreleased
     */
    public function register()
    {
        AdminNotices::initialize('givewp', GIVE_PLUGIN_URL . 'vendor/vendor-prefixed/stellarwp/admin-notices/');
    }

    /**
     * {@inheritDoc}
     *
     * @unreleased
     */
    public function boot()
    {
    }
}
