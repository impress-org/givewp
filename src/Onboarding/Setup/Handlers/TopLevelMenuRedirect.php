<?php

namespace Give\Onboarding\Setup\Handlers;

defined('ABSPATH') || exit;

/**
 * Redirect the top level "Donations" menu to the Setup submenu.
 * This normalizes the Setup Page URL so that assets load correctly.
 */
class TopLevelMenuRedirect implements RequestHandler
{

    /**
     * @inheritdoc
     *
     * @since 2.8.0
     */
    public function maybeHandle()
    {
        if (isset($_GET['page']) && 'give-setup' == $_GET['page'] && ! isset($_GET['post_type'])) {
            $this->handle();
        }
    }

    /**
     * @inheritdoc
     *
     * @since 2.8.0
     */
    public function handle()
    {
        wp_redirect(
            $this->getRedirectUrl()
        );
        exit;
    }

    /**
     * @since 2.8.0
     */
    protected function getRedirectUrl()
    {
        return add_query_arg(
            [
                'post_type' => 'give_forms',
                'page' => 'give-setup',
            ],
            admin_url('edit.php')
        );
    }
}
