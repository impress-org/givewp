<?php

declare(strict_types=1);

namespace Give\VendorOverrides\CoreUpdateNotice;

use Give\ServiceProviders\ServiceProvider;
use Give\Vendors\StellarWP\CoreUpdateNotice\Config;
use Give\Vendors\StellarWP\CoreUpdateNotice\Register;

/**
 * Registers and boots the Core Update Notice library
 *
 * @since TBD
 *
 * @see https://github.com/stellarwp/core-update-notice
 */
class CoreUpdateNoticeServiceProvider implements ServiceProvider
{
    /**
     * {@inheritDoc}
     *
     * @since TBD
     */
    public function register()
    {
        Config::setContainer(give()->getContainer());
    }

    /**
     * {@inheritDoc}
     *
     * @since TBD
     */
    public function boot()
    {
        /* Deferred to init because the copy below is only translatable once the text domain loads. */
        add_action('init', static function () {
            Register::notice([
                'heading' => __('Keep your site protected. Update to the latest version of WordPress.', 'give'),
                'body' => __(
                    'Your site is running on an outdated version of WordPress, which can leave it vulnerable to security issues. To decrease your risk of exposure, please update your WordPress install to the latest version.',
                    'give'
                ),
                'dismiss' => __('Dismiss this notice.', 'give'),
            ]);
        });
    }
}
