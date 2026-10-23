<?php

namespace Give\Campaigns\Actions;

use WP_Admin_Bar;

/**
 * Points the admin bar's "New > Campaign" item at the create campaign screen.
 *
 * @since TBD
 */
class ReplaceGiveFormsAdminBarNewLink
{
    /**
     * @since TBD
     */
    public function __invoke(WP_Admin_Bar $wpAdminBar): void
    {
        if (!$wpAdminBar->get_node('new-give_forms')) {
            return;
        }

        $wpAdminBar->add_node([
            'id' => 'new-give_forms',
            'href' => admin_url('edit.php?post_type=give_forms&page=give-campaigns&new=campaign'),
        ]);
    }
}
