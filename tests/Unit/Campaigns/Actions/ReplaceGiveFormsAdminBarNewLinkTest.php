<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Tests\TestCase;
use WP_Admin_Bar;

/**
 * @since TBD
 *
 * @covers \Give\Campaigns\Actions\ReplaceGiveFormsAdminBarNewLink
 */
final class ReplaceGiveFormsAdminBarNewLinkTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testNewCampaignItemOpensCreateCampaignScreen(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));

        $node = $this->buildAdminBar()->get_node('new-give_forms');

        $this->assertSame('Campaign', $node->title);
        $this->assertSame(
            admin_url('edit.php?post_type=give_forms&page=give-campaigns&new=campaign'),
            $node->href
        );
    }

    /**
     * @since TBD
     */
    public function testNoNewCampaignItemForUsersWhoCannotCreateForms(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'subscriber']));

        $this->assertNull($this->buildAdminBar()->get_node('new-give_forms'));
    }

    /**
     * @since TBD
     */
    private function buildAdminBar(): WP_Admin_Bar
    {
        require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';

        $adminBar = new WP_Admin_Bar();
        wp_admin_bar_new_content_menu($adminBar);
        do_action_ref_array('admin_bar_menu', [&$adminBar]);

        return $adminBar;
    }
}
