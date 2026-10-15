<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\LoadCampaignDetailsAssets;
use Give\Campaigns\Actions\LoadCampaignsListTableAssets;
use Give\Campaigns\Models\Campaign;
use Give\Framework\Permissions\Facades\UserPermissions;

/**
 * @since 4.0.0
 */
class CampaignsAdminPage
{
    /**
     * @since 4.14.0 update permission capability to use facade
     * @since 4.0.0
     */
    public function addCampaignsSubmenuPage()
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Campaigns', 'give'),
            esc_html__('Campaigns', 'give'),
            UserPermissions::campaigns()->viewCap(),
            'give-campaigns',
            [$this, 'renderCampaignsPage'],
            0
        );
    }

    /**
     * @since TBD Escape output. Guard the id param.
     * @since 4.0.0
     */
    public function renderCampaignsPage()
    {
        if (self::isShowingDetailsPage()) {
            $campaign = Campaign::find(absint($_GET['id'] ?? 0)); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only id param; it only selects which record to show and saves nothing.

            if ( ! $campaign) {
                wp_die(esc_html__('Campaign not found', 'give'), 404);
            }

            give(LoadCampaignDetailsAssets::class)();
        } else {
            give(LoadCampaignsListTableAssets::class)();
        }

        echo '<div id="give-admin-campaigns-root"></div>';
    }

    /**
     * @since 4.0.0
     */
    public static function isShowingDetailsPage(): bool
    {
        return isset($_GET['id'], $_GET['page']) && 'give-campaigns' === $_GET['page']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page check; it only decides what to show and saves nothing.
    }

    /**
     * @since 4.10.0
     */
    public static function getUrl()
    {
        return admin_url('edit.php?post_type=give_forms&page=give-campaigns');
    }
}
