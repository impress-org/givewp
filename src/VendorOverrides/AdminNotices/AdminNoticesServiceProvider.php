<?php

declare(strict_types=1);

namespace Give\VendorOverrides\AdminNotices;

use Give\ServiceProviders\ServiceProvider;
use Give\Vendors\StellarWP\AdminNotices\AdminNotices;

class AdminNoticesServiceProvider implements ServiceProvider {
    public function register()
    {
        AdminNotices::initialize('givewp', GIVE_PLUGIN_URL . 'vendor/vendor-prefixed/stellarwp/admin-notices/');
    }

    public function boot()
    {
    }
}
