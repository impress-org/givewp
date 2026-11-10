<?php

/**
 * Onboarding class
 *
 * @package Give
 */

namespace Give\Onboarding\Setup\Handlers;

defined('ABSPATH') || exit;

class AdminNoticeHandler implements RequestHandler
{

    /**
     * @inheritDoc
     */
    public function maybeHandle()
    {
        if ( ! isset($_GET['page']) || 'give-setup' !== $_GET['page']) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page check; it only decides whether the setup notice is shown and saves nothing.
            return;
        }

        $this->handle();
    }

    /**
     * @inheritDoc
     */
    public function handle()
    {
        add_action(
            'admin_notices',
            function () {
                ob_start();
            },
            -999999
        );
        add_action(
            'admin_notices',
            function () {
                ob_get_clean();
            },
            999999
        );
    }
}
