<?php

declare(strict_types=1);

namespace Give\VendorOverrides\CoreUpdateNotice;

use Give\ServiceProviders\ServiceProvider;
use Give\Vendors\StellarWP\CoreUpdateNotice\CoreUpdateNotice;
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
        /* The notice is registered on init in boot(), once the text domain has loaded. */
    }

    /**
     * {@inheritDoc}
     *
     * @since TBD
     */
    public function boot()
    {
        add_action('init', static function () {
            Register::notice(
                new CoreUpdateNotice([
                    'heading' => __(
                        'Keep your site protected. Update to the latest version of WordPress.',
                        'give'
                    ),
                    'body' => __(
                        'Your site is running on an outdated version of WordPress, which can leave it vulnerable to security issues. To decrease your risk of exposure, please update your WordPress install to the latest version.',
                        'give'
                    ),
                    'dismiss' => __('Dismiss this notice.', 'give'),
                ]),
                static function (): bool {
                    $screen = get_current_screen();

                    return $screen !== null && (
                        $screen->post_type === 'give_forms'
                        || strpos($screen->id, 'give_forms_page_') === 0
                        || strpos($screen->id, 'admin_page_give-') === 0
                    );
                }
            );
        });
    }
}
