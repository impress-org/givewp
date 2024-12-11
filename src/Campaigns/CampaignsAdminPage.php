<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\LoadCampaignDetailsAssets;
use Give\Campaigns\Actions\LoadCampaignsListTableAssets;
use Give\Campaigns\Models\Campaign;

/**
 * @unreleased
 */
class CampaignsAdminPage
{
    /**
     * @unreleased
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
     * @unreleased
     */
    public function renderCampaignsPage()
    {
        if (self::isShowingDetailsPage()) {
            $campaign = Campaign::find(absint($_GET['id']));

            if ( ! $campaign) {
                wp_die(__('Campaign not found', 'give'), 404);
            }

            give(LoadCampaignDetailsAssets::class)($campaign);
        } else {
            give(LoadCampaignsListTableAssets::class)();
        }

        echo '<div id="give-admin-campaigns-root"></div>';
    }

    /**
     * @unreleased
     */
    public static function isShowingDetailsPage(): bool
    {
        return isset($_GET['id']) && isset($_GET['page']) && 'give-campaigns' == isset($_GET['page']);
    }
}
