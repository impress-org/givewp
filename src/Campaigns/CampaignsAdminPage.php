<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\LoadCampaignDetailsAssets;
use Give\Campaigns\Actions\LoadCampaignsListTableAssets;
use Give\Campaigns\Models\Campaign;

/**
 * @since 4.0.0
 */
class CampaignsAdminPage
{
    /**
     * @since 4.0.0
     */
    public function addCampaignsSubmenuPage()
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Campaigns', 'give'),
            esc_html__('Campaigns', 'give'),
            'edit_give_forms',
            'give-campaigns',
            [$this, 'renderCampaignsPage'],
            0
        );
    }

    /**
     * @since 4.0.0
     */
    public function renderCampaignsPage()
    {
        if (self::isShowingDetailsPage()) {
            $campaign = Campaign::find(absint($_GET['id']));

            if ( ! $campaign) {
                wp_die(__('Campaign not found', 'give'), 404);
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
        return isset($_GET['id'], $_GET['page']) && 'give-campaigns' === $_GET['page'];
    }

    /**
     * @since 4.10.0
     */
    public static function getUrl()
    {
        return admin_url('edit.php?post_type=give_forms&page=give-campaigns');
    }
}
